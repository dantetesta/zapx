-- ============================================
-- Sistema de Disparo em Massa WhatsApp - ZAPX
-- Autor: Dante Testa (https://dantetesta.com.br)
-- Data: 2025-10-25 20:36:00
-- Versão: 3.0.0
-- ============================================
--
-- CHANGELOG:
-- v3.1.0 (2025-10-25 20:50):
--   - Adicionado campo timezone na tabela users
--   - Permite configurar fuso horário individual por usuário
--   - Padrão: America/Sao_Paulo (Brasília)
--
-- v3.0.0 (2025-10-25 20:36):
--   - Adicionado suporte internacional completo
--   - Campo default_country_code na tabela users (DDI padrão)
--   - Campo country_code na tabela contacts (código do país)
--   - Números armazenados com DDI completo (ex: 5511999999999)
--   - Suporte a importação de contatos internacionais
--   - Consolidados scripts: add_default_country_code.sql e add_international_support.sql
--
-- v2.0.0 (2025-10-25 14:47):
--   - Adicionados campos de limite de mensagens mensais
--   - Adicionado campo evolution_created_at
--   - Sistema de controle de mensagens mensais
--
-- v1.0.0 (2025-10-25 07:13):
--   - Estrutura inicial do banco de dados
--   - Tabelas: users, tags, contacts, contact_tags, dispatch_history
--
-- ============================================

CREATE DATABASE IF NOT EXISTS zapx_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE zapx_system;

-- Tabela de usuários
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    is_admin TINYINT(1) DEFAULT 0,
    default_country_code VARCHAR(5) DEFAULT '55' COMMENT 'DDI padrão para importação de contatos',
    timezone VARCHAR(50) DEFAULT 'America/Sao_Paulo' COMMENT 'Timezone do usuário',
    -- Campos da instância Evolution (API centralizada no config.php)
    evolution_instance VARCHAR(255) DEFAULT NULL,
    evolution_instance_token VARCHAR(255) DEFAULT NULL,
    evolution_phone_number VARCHAR(20) DEFAULT NULL,
    evolution_status VARCHAR(50) DEFAULT NULL,
    evolution_qrcode TEXT DEFAULT NULL,
    evolution_created_at TIMESTAMP NULL DEFAULT NULL,
    -- Sistema de limite de mensagens mensais
    message_limit INT DEFAULT 1000 COMMENT 'Limite mensal de mensagens',
    messages_sent INT DEFAULT 0 COMMENT 'Mensagens enviadas no mês atual',
    limit_reset_date DATE DEFAULT (CURRENT_DATE) COMMENT 'Data do último reset do contador',
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_limit_reset (limit_reset_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de tags/categorias
CREATE TABLE IF NOT EXISTS tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    color VARCHAR(7) DEFAULT '#3B82F6',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de contatos
CREATE TABLE IF NOT EXISTS contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(255) DEFAULT NULL,
    phone VARCHAR(20) NOT NULL COMMENT 'Número completo com DDI (ex: 5511999999999)',
    country_code VARCHAR(5) DEFAULT 'BR' COMMENT 'Código do país (ISO 3166-1 alpha-2)',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_phone (phone),
    INDEX idx_country_code (country_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de relacionamento contatos e tags
CREATE TABLE IF NOT EXISTS contact_tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contact_id INT NOT NULL,
    tag_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (contact_id) REFERENCES contacts(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE,
    UNIQUE KEY unique_contact_tag (contact_id, tag_id),
    INDEX idx_contact_id (contact_id),
    INDEX idx_tag_id (tag_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de histórico de disparos
CREATE TABLE IF NOT EXISTS dispatch_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    contact_id INT NOT NULL,
    message TEXT NOT NULL,
    media_type ENUM('text', 'image', 'video', 'audio', 'document') DEFAULT 'text',
    thumbnail_path VARCHAR(255) DEFAULT NULL,
    status ENUM('pending', 'sent', 'failed') DEFAULT 'pending',
    error_message TEXT DEFAULT NULL,
    sent_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (contact_id) REFERENCES contacts(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at),
    INDEX idx_media_type (media_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de tokens de reset de senha
CREATE TABLE IF NOT EXISTS password_reset_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    used TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

