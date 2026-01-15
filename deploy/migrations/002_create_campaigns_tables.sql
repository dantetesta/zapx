-- ============================================
-- Migração: Sistema de Campanhas Back-end
-- Autor: Dante Testa (https://dantetesta.com.br)
-- Data: 2026-01-14 23:10:00
-- Versão: 3.3.0
-- ============================================

-- Tabela de Campanhas de Disparo
CREATE TABLE IF NOT EXISTS dispatch_campaigns (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(255) DEFAULT NULL COMMENT 'Nome da campanha (opcional)',
    message TEXT NOT NULL COMMENT 'Mensagem a ser enviada',
    media_type ENUM('text','image','video','audio','document') DEFAULT 'text',
    media_path VARCHAR(255) DEFAULT NULL COMMENT 'Caminho do arquivo de mídia',
    media_filename VARCHAR(255) DEFAULT NULL COMMENT 'Nome original do arquivo',
    status ENUM('pending','running','paused','completed','cancelled') DEFAULT 'pending',
    total_contacts INT DEFAULT 0 COMMENT 'Total de contatos na campanha',
    sent_count INT DEFAULT 0 COMMENT 'Mensagens enviadas com sucesso',
    failed_count INT DEFAULT 0 COMMENT 'Mensagens que falharam',
    min_interval INT DEFAULT 3 COMMENT 'Intervalo mínimo entre mensagens (segundos)',
    max_interval INT DEFAULT 20 COMMENT 'Intervalo máximo entre mensagens (segundos)',
    tag_id INT DEFAULT NULL COMMENT 'Tag usada para filtrar contatos (null = todos)',
    started_at TIMESTAMP NULL COMMENT 'Quando a campanha iniciou',
    paused_at TIMESTAMP NULL COMMENT 'Quando a campanha foi pausada',
    completed_at TIMESTAMP NULL COMMENT 'Quando a campanha finalizou',
    last_processed_at TIMESTAMP NULL COMMENT 'Última vez que processou um item',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE SET NULL,
    INDEX idx_user_status (user_id, status),
    INDEX idx_status (status),
    INDEX idx_last_processed (last_processed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Fila de Disparo (itens individuais)
CREATE TABLE IF NOT EXISTS dispatch_queue (
    id INT AUTO_INCREMENT PRIMARY KEY,
    campaign_id INT NOT NULL,
    contact_id INT NOT NULL,
    status ENUM('pending','processing','sent','failed','cancelled') DEFAULT 'pending',
    scheduled_at TIMESTAMP NULL COMMENT 'Quando será enviado',
    sent_at TIMESTAMP NULL COMMENT 'Quando foi enviado',
    error_message TEXT DEFAULT NULL COMMENT 'Mensagem de erro se falhou',
    attempts INT DEFAULT 0 COMMENT 'Tentativas de envio',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (campaign_id) REFERENCES dispatch_campaigns(id) ON DELETE CASCADE,
    FOREIGN KEY (contact_id) REFERENCES contacts(id) ON DELETE CASCADE,
    INDEX idx_campaign_status (campaign_id, status),
    INDEX idx_status_scheduled (status, scheduled_at),
    INDEX idx_processing (status, scheduled_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Lock para evitar processamento concorrente
CREATE TABLE IF NOT EXISTS dispatch_locks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    campaign_id INT NOT NULL UNIQUE,
    locked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    locked_by VARCHAR(100) DEFAULT NULL COMMENT 'Identificador do processo',
    FOREIGN KEY (campaign_id) REFERENCES dispatch_campaigns(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
