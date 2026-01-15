#!/bin/bash
# ============================================
# Script de Validação Pós-Deploy - ZAPX
# Autor: Dante Testa (https://dantetesta.com.br)
# Data: 2026-01-14 20:35:00
# ============================================

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
source "$SCRIPT_DIR/.env.deploy"

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Criar diretório de logs
mkdir -p "$SCRIPT_DIR/logs"
LOG_FILE="$SCRIPT_DIR/logs/validate_$(date +%Y%m%d_%H%M%S).log"

log() {
    local level="$1"
    local message="$2"
    local timestamp=$(date '+%Y-%m-%d %H:%M:%S')
    echo -e "[$timestamp] [$level] $message" | tee -a "$LOG_FILE"
}

log_success() { log "SUCCESS" "${GREEN}$1${NC}"; }
log_error() { log "ERROR" "${RED}$1${NC}"; }
log_info() { log "INFO" "${BLUE}$1${NC}"; }
log_warning() { log "WARNING" "${YELLOW}$1${NC}"; }

echo -e "${BLUE}"
echo "============================================"
echo "       ZAPX - Validação Pós-Deploy"
echo "============================================"
echo -e "${NC}"

log_info "Iniciando validação pós-deploy..."
log_info "URL: $APP_URL"

TESTS_PASSED=0
TESTS_FAILED=0

# Função para testar endpoint
test_endpoint() {
    local url="$1"
    local expected_code="$2"
    local description="$3"
    
    log_info "Testando: $description"
    
    HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" --max-time 30 "$url" 2>/dev/null || echo "000")
    
    if [ "$HTTP_CODE" -eq "$expected_code" ]; then
        log_success "$description - HTTP $HTTP_CODE ✓"
        ((TESTS_PASSED++))
        return 0
    else
        log_error "$description - HTTP $HTTP_CODE (esperado: $expected_code) ✗"
        ((TESTS_FAILED++))
        return 1
    fi
}

# Função para testar conexão com banco
test_database() {
    log_info "Testando conexão com banco de dados..."
    
    if mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SELECT 1" > /dev/null 2>&1; then
        log_success "Conexão com banco de dados OK ✓"
        ((TESTS_PASSED++))
        
        # Verificar tabelas principais
        TABLES=$(mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SHOW TABLES" 2>/dev/null | tail -n +2 | wc -l)
        log_info "Tabelas encontradas: $TABLES"
        
        return 0
    else
        log_error "Falha na conexão com banco de dados ✗"
        ((TESTS_FAILED++))
        return 1
    fi
}

echo ""
log_info "=== TESTES DE ENDPOINT ==="

# Testar página principal (deve redirecionar para login ou mostrar dashboard)
test_endpoint "$APP_URL" 200 "Página Principal"

# Testar página de login
test_endpoint "$APP_URL/auth/login" 200 "Página de Login"

# Testar assets estáticos (se existirem)
test_endpoint "$APP_URL/public/css/style.css" 200 "CSS Principal" || true

echo ""
log_info "=== TESTES DE BANCO DE DADOS ==="

# Testar banco de dados
test_database

echo ""
log_info "=== TESTES DE SSL ==="

# Verificar SSL
log_info "Verificando certificado SSL..."
if curl -s --head "$APP_URL" 2>/dev/null | grep -q "HTTP/2"; then
    log_success "HTTP/2 habilitado ✓"
    ((TESTS_PASSED++))
else
    log_warning "HTTP/2 não detectado (pode estar usando HTTP/1.1)"
fi

# Verificar redirecionamento HTTPS
log_info "Verificando redirecionamento HTTPS..."
HTTP_URL="${APP_URL/https:/http:}"
REDIRECT=$(curl -s -o /dev/null -w "%{redirect_url}" --max-time 10 "$HTTP_URL" 2>/dev/null || echo "")

if [[ "$REDIRECT" == https://* ]]; then
    log_success "Redirecionamento HTTP->HTTPS OK ✓"
    ((TESTS_PASSED++))
else
    log_warning "Redirecionamento HTTPS não detectado"
fi

echo ""
echo -e "${BLUE}============================================${NC}"
echo -e "${BLUE}       Resultado da Validação${NC}"
echo -e "${BLUE}============================================${NC}"

if [ "$TESTS_FAILED" -eq 0 ]; then
    echo -e "  Status: ${GREEN}TODOS OS TESTES PASSARAM!${NC}"
else
    echo -e "  Status: ${YELLOW}ALGUNS TESTES FALHARAM${NC}"
fi

echo -e "  Passaram: ${GREEN}$TESTS_PASSED${NC}"
echo -e "  Falharam: ${RED}$TESTS_FAILED${NC}"
echo -e "  Log: $LOG_FILE"
echo ""

if [ "$TESTS_FAILED" -gt 0 ]; then
    log_warning "Validação concluída com $TESTS_FAILED falha(s)"
    exit 1
else
    log_success "Validação concluída com sucesso!"
    exit 0
fi
