#!/bin/bash
# ============================================
# Script Orquestrador - ZAPX AutoDeploy
# Autor: Dante Testa (https://dantetesta.com.br)
# Data: 2026-01-14 20:35:00
# ============================================
# Fluxo: MIGRAR -> SEED -> DEPLOYAR -> VALIDAR

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
source "$SCRIPT_DIR/.env.deploy"

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
MAGENTA='\033[0;35m'
NC='\033[0m'

# Criar diretório de logs
mkdir -p "$SCRIPT_DIR/logs"
LOG_FILE="$SCRIPT_DIR/logs/run-all_$(date +%Y%m%d_%H%M%S).log"

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
log_step() { log "STEP" "${MAGENTA}$1${NC}"; }

echo -e "${MAGENTA}"
echo "╔════════════════════════════════════════════╗"
echo "║     ZAPX - AutoDeploy + AutoMigration      ║"
echo "║     Autor: Dante Testa                     ║"
echo "╚════════════════════════════════════════════╝"
echo -e "${NC}"

START_TIME=$(date +%s)

log_info "Iniciando pipeline de deploy..."
log_info "Ambiente: $APP_ENV"
log_info "URL: $APP_URL"
log_info "Database: $DB_NAME @ $DB_HOST"

# ============================================
# ETAPA 1: MIGRAÇÕES
# ============================================
echo ""
log_step "═══ ETAPA 1/4: MIGRAÇÕES ═══"

if bash "$SCRIPT_DIR/migrate.sh"; then
    log_success "Migrações concluídas!"
else
    log_error "Falha nas migrações! Abortando deploy."
    exit 1
fi

# ============================================
# ETAPA 2: SEEDS (se habilitado)
# ============================================
echo ""
log_step "═══ ETAPA 2/4: SEEDS ═══"

if [ "$INCLUDE_SEED" = true ]; then
    if bash "$SCRIPT_DIR/seed.sh"; then
        log_success "Seeds concluídos!"
    else
        log_warning "Seeds falharam, mas continuando deploy..."
    fi
else
    log_info "Seeds desabilitados (INCLUDE_SEED=false)"
fi

# ============================================
# ETAPA 3: DEPLOY FTP
# ============================================
echo ""
log_step "═══ ETAPA 3/4: DEPLOY FTP ═══"

if bash "$SCRIPT_DIR/deploy.sh"; then
    log_success "Deploy FTP concluído!"
else
    log_error "Falha no deploy FTP!"
    exit 1
fi

# ============================================
# ETAPA 4: VALIDAÇÃO
# ============================================
echo ""
log_step "═══ ETAPA 4/4: VALIDAÇÃO ═══"

if [ "$VALIDATE_AFTER_DEPLOY" = true ]; then
    # Aguardar propagação
    log_info "Aguardando 5 segundos para propagação..."
    sleep 5
    
    if bash "$SCRIPT_DIR/validate.sh"; then
        log_success "Validação concluída!"
    else
        log_warning "Validação falhou! Verifique os logs."
    fi
else
    log_info "Validação desabilitada (VALIDATE_AFTER_DEPLOY=false)"
fi

# ============================================
# RESUMO FINAL
# ============================================
END_TIME=$(date +%s)
DURATION=$((END_TIME - START_TIME))

echo ""
echo -e "${GREEN}╔════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║         DEPLOY CONCLUÍDO COM SUCESSO!      ║${NC}"
echo -e "${GREEN}╚════════════════════════════════════════════╝${NC}"
echo ""
echo -e "  ${BLUE}URL:${NC} $APP_URL"
echo -e "  ${BLUE}Tempo total:${NC} ${DURATION}s"
echo -e "  ${BLUE}Log:${NC} $LOG_FILE"
echo ""

log_success "Pipeline de deploy finalizado em ${DURATION}s"

# Limpar cache do navegador (instrução)
echo -e "${YELLOW}💡 Dica: Limpe o cache do navegador (Ctrl+Shift+R) para ver as alterações${NC}"
echo ""

exit 0
