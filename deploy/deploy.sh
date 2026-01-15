#!/bin/bash
# ============================================
# Script de Deploy FTP - ZAPX
# Autor: Dante Testa (https://dantetesta.com.br)
# Data: 2026-01-14 20:35:00
# ============================================

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"
source "$SCRIPT_DIR/.env.deploy"

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Criar diretório de logs
mkdir -p "$SCRIPT_DIR/logs"
LOG_FILE="$SCRIPT_DIR/logs/deploy_$(date +%Y%m%d_%H%M%S).log"

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
echo "       ZAPX - Deploy FTP"
echo "============================================"
echo -e "${NC}"

log_info "Iniciando processo de deploy..."
log_info "Host: $FTP_HOST | Root: $FTP_ROOT"
log_info "Projeto: $PROJECT_DIR"

# Arquivos e diretórios a excluir do deploy
EXCLUDE_LIST=(
    ".git"
    ".gitignore"
    "deploy"
    "node_modules"
    ".env"
    ".env.deploy"
    "*.log"
    ".DS_Store"
    "Thumbs.db"
    "error_log"
    ".ftpquota"
    "*.zip"
    ".well-known"
)

# Criar arquivo de exclusão temporário
EXCLUDE_FILE=$(mktemp)
for item in "${EXCLUDE_LIST[@]}"; do
    echo "$item" >> "$EXCLUDE_FILE"
done

# Verificar se lftp está instalado
if ! command -v lftp &> /dev/null; then
    log_error "lftp não está instalado! Instale com: brew install lftp (macOS) ou apt install lftp (Linux)"
    exit 1
fi

log_info "Sincronizando arquivos via FTP..."

# Deploy usando lftp (mirror reverso)
lftp -u "$FTP_USER","$FTP_PASS" "ftp://$FTP_HOST:$FTP_PORT" << EOF
set ssl:verify-certificate no
set ftp:ssl-allow no
set ftp:ssl-force no
set net:timeout 30
set net:max-retries 3
set net:reconnect-interval-base 5

cd $FTP_ROOT

# Mirror reverso (local -> remoto)
mirror --reverse \
    --verbose \
    --delete \
    --parallel=3 \
    --exclude-glob .git \
    --exclude-glob .git/** \
    --exclude-glob deploy/** \
    --exclude-glob node_modules/** \
    --exclude-glob .env \
    --exclude-glob .env.* \
    --exclude-glob *.log \
    --exclude-glob .DS_Store \
    --exclude-glob Thumbs.db \
    --exclude-glob error_log \
    --exclude-glob .ftpquota \
    --exclude-glob *.zip \
    --exclude-glob .well-known/** \
    "$PROJECT_DIR" .

bye
EOF

# Limpar arquivo temporário
rm -f "$EXCLUDE_FILE"

log_success "Deploy FTP concluído!"

echo ""
echo -e "${GREEN}============================================${NC}"
echo -e "${GREEN}       Deploy Concluído!${NC}"
echo -e "${GREEN}============================================${NC}"
echo -e "  Log: $LOG_FILE"
echo ""

exit 0
