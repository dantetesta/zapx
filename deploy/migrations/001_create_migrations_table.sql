-- ============================================
-- Migração: Criar tabela de controle de migrações
-- Autor: Dante Testa (https://dantetesta.com.br)
-- Data: 2026-01-14 20:35:00
-- ============================================

CREATE TABLE IF NOT EXISTS migrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    migration VARCHAR(255) NOT NULL UNIQUE,
    batch INT NOT NULL DEFAULT 1,
    executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_migration (migration),
    INDEX idx_batch (batch)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
