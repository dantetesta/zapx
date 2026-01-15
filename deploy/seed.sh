#!/bin/bash
# ============================================
# Script de Seeds - ZAPX
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
LOG_FILE="$SCRIPT_DIR/logs/seed_$(date +%Y%m%d_%H%M%S).log"

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
echo "       ZAPX - Sistema de Seeds"
echo "============================================"
echo -e "${NC}"

log_info "Iniciando processo de seed..."
log_info "Host: $DB_HOST | Database: $DB_NAME"

# Função para executar arquivo SQL
execute_sql_file() {
    local file="$1"
    mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$file" 2>/dev/null
}

# Testar conexão
log_info "Testando conexão com o banco de dados..."
if ! mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SELECT 1" > /dev/null 2>&1; then
    log_error "Falha na conexão com o banco de dados!"
    exit 1
fi
log_success "Conexão estabelecida com sucesso!"

# Processar seeds
SEEDS_DIR="$SCRIPT_DIR/seeds"
SEEDED=0

if [ -d "$SEEDS_DIR" ]; then
    for seed_file in "$SEEDS_DIR"/*.sql; do
        if [ -f "$seed_file" ]; then
            seed_name=$(basename "$seed_file")
            log_info "Executando seed: $seed_name"
            
            if execute_sql_file "$seed_file"; then
                log_success "Seed $seed_name executado com sucesso!"
                ((SEEDED++))
            else
                log_warning "Seed $seed_name pode ter falhado ou dados já existem"
            fi
        fi
    done
else
    log_warning "Diretório de seeds não encontrado: $SEEDS_DIR"
fi

echo ""
echo -e "${GREEN}============================================${NC}"
echo -e "${GREEN}       Seeds Concluídos!${NC}"
echo -e "${GREEN}============================================${NC}"
echo -e "  Executados: ${GREEN}$SEEDED${NC}"
echo -e "  Log: $LOG_FILE"
echo ""

log_success "Processo de seed finalizado! Executados: $SEEDED"
exit 0
