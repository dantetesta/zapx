<?php
/**
 * Migração 005 - Adicionar tracking de delay
 * Autor: Dante Testa (https://dantetesta.com.br)
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/Database.php';

header('Content-Type: text/plain; charset=utf-8');

try {
    $db = Database::getInstance()->getConnection();
    
    echo "=== Migração 005: Tracking de Delay ===\n\n";
    
    // Verificar se colunas já existem
    $result = $db->query("SHOW COLUMNS FROM dispatch_campaigns LIKE 'next_delay'");
    if ($result->rowCount() > 0) {
        echo "⚠️ Colunas já existem, migração já foi executada.\n";
    } else {
        $db->exec("
            ALTER TABLE dispatch_campaigns 
            ADD COLUMN next_delay INT DEFAULT NULL COMMENT 'Próximo delay sorteado em segundos',
            ADD COLUMN next_send_at TIMESTAMP NULL DEFAULT NULL COMMENT 'Timestamp do próximo envio'
        ");
        echo "✅ Colunas next_delay e next_send_at adicionadas!\n";
    }
    
    echo "\n=== MIGRAÇÃO CONCLUÍDA! ===\n";
    echo "⚠️ Delete este arquivo após executar.\n";
    
} catch (PDOException $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
}
