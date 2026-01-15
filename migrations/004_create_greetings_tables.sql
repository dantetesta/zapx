-- Migração: Criar tabelas de saudações personalizadas
-- Autor: Dante Testa (https://dantetesta.com.br)
-- Data: 2026-01-15 18:32:00

-- Tabela de Saudações Personalizadas por Usuário
CREATE TABLE IF NOT EXISTS user_greetings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    template VARCHAR(500) NOT NULL COMMENT 'Template com macros {periodo}, {nome}, {numero}',
    is_active TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_active (user_id, is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela para controlar índice atual de saudação por campanha
CREATE TABLE IF NOT EXISTS campaign_greeting_index (
    campaign_id INT PRIMARY KEY,
    current_index INT DEFAULT 0,
    FOREIGN KEY (campaign_id) REFERENCES dispatch_campaigns(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
