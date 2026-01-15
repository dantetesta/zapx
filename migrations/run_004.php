<?php
/**
 * Script para executar migração 004 - Tabelas de Saudações
 * Autor: Dante Testa (https://dantetesta.com.br)
 * Data: 2026-01-15 18:32:00
 * 
 * Acesse: https://zap.dantetesta.com.br/migrations/run_004.php
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/Database.php';

header('Content-Type: text/plain; charset=utf-8');

try {
    $db = Database::getInstance()->getConnection();
    
    echo "=== Migração 004: Tabelas de Saudações ===\n\n";
    
    // Criar tabela user_greetings
    echo "1. Criando tabela user_greetings...\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS user_greetings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            template VARCHAR(500) NOT NULL COMMENT 'Template com macros {periodo}, {nome}, {numero}',
            is_active TINYINT(1) DEFAULT 1,
            sort_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_user_active (user_id, is_active, sort_order)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "   ✅ Tabela user_greetings criada!\n\n";
    
    // Criar tabela campaign_greeting_index
    echo "2. Criando tabela campaign_greeting_index...\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS campaign_greeting_index (
            campaign_id INT PRIMARY KEY,
            current_index INT DEFAULT 0,
            FOREIGN KEY (campaign_id) REFERENCES dispatch_campaigns(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "   ✅ Tabela campaign_greeting_index criada!\n\n";
    
    echo "=== MIGRAÇÃO CONCLUÍDA COM SUCESSO! ===\n\n";
    echo "Agora você pode:\n";
    echo "1. Acessar /greeting/index para configurar suas saudações\n";
    echo "2. Usar o macro {saudacao} nas campanhas\n";
    echo "\n⚠️ IMPORTANTE: Delete este arquivo após executar!\n";
    
} catch (PDOException $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
}
