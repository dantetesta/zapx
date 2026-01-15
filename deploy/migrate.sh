#!/bin/bash
# ============================================
# Script de Migrações - ZAPX
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
LOG_FILE="$SCRIPT_DIR/logs/migrate_$(date +%Y%m%d_%H%M%S).log"

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
echo "       ZAPX - Sistema de Migrações"
echo "============================================"
echo -e "${NC}"

log_info "Iniciando processo de migração..."
log_info "Host: $DB_HOST | Database: $DB_NAME"

# Função para executar SQL
execute_sql() {
    local sql="$1"
    mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "$sql" 2>/dev/null
}

# Função para executar arquivo SQL
execute_sql_file() {
    local file="$1"
    mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$file" 2>/dev/null
}

# Testar conexão
log_info "Testando conexão com o banco de dados..."
if ! execute_sql "SELECT 1" > /dev/null 2>&1; then
    log_error "Falha na conexão com o banco de dados!"
    exit 1
fi
log_success "Conexão estabelecida com sucesso!"

# Criar tabela de migrações se não existir
log_info "Verificando tabela de migrações..."
execute_sql "
CREATE TABLE IF NOT EXISTS migrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    migration VARCHAR(255) NOT NULL UNIQUE,
    batch INT NOT NULL DEFAULT 1,
    executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_migration (migration),
    INDEX idx_batch (batch)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
"
log_success "Tabela de migrações verificada!"

# Obter próximo batch
BATCH=$(execute_sql "SELECT COALESCE(MAX(batch), 0) + 1 FROM migrations;" | tail -n1)
log_info "Batch atual: $BATCH"

# Processar migrações
MIGRATIONS_DIR="$SCRIPT_DIR/migrations"
MIGRATED=0
SKIPPED=0

if [ -d "$MIGRATIONS_DIR" ]; then
    for migration_file in "$MIGRATIONS_DIR"/*.sql; do
        if [ -f "$migration_file" ]; then
            migration_name=$(basename "$migration_file")
            
            # Verificar se já foi executada
            EXISTS=$(execute_sql "SELECT COUNT(*) FROM migrations WHERE migration = '$migration_name';" | tail -n1)
            
            if [ "$EXISTS" -eq 0 ]; then
                log_info "Executando: $migration_name"
                
                if execute_sql_file "$migration_file"; then
                    # Registrar migração
                    execute_sql "INSERT INTO migrations (migration, batch) VALUES ('$migration_name', $BATCH);"
                    log_success "Migração $migration_name executada com sucesso!"
                    ((MIGRATED++))
                else
                    log_error "Falha ao executar migração: $migration_name"
                    exit 1
                fi
            else
                log_warning "Pulando: $migration_name (já executada)"
                ((SKIPPED++))
            fi
        fi
    done
else
    log_warning "Diretório de migrações não encontrado: $MIGRATIONS_DIR"
fi

echo ""
echo -e "${GREEN}============================================${NC}"
echo -e "${GREEN}       Migrações Concluídas!${NC}"
echo -e "${GREEN}============================================${NC}"
echo -e "  Executadas: ${GREEN}$MIGRATED${NC}"
echo -e "  Ignoradas:  ${YELLOW}$SKIPPED${NC}"
echo -e "  Log: $LOG_FILE"
echo ""

log_success "Processo de migração finalizado! Executadas: $MIGRATED | Ignoradas: $SKIPPED"
exit 0
