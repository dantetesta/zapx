<?php
/**
 * Cron Job - Processador de Fila
 * Autor: Dante Testa (https://dantetesta.com.br)
 * Data: 2026-01-14 23:10:00
 * 
 * Executar via cron a cada minuto:
 * * * * * * php /path/to/cron/process_queue.php >> /path/to/logs/queue.log 2>&1
 */

// Definir diretório base
define('BASE_PATH', dirname(__DIR__));

// Evitar execução via navegador (apenas CLI)
if (php_sapi_name() !== 'cli') {
    die('Este script deve ser executado via linha de comando');
}

// Carregar configurações
require_once BASE_PATH . '/config/config.php';
require_once BASE_PATH . '/config/Database.php';
require_once BASE_PATH . '/core/QueueProcessor.php';

// Log de início
$timestamp = date('Y-m-d H:i:s');
echo "[$timestamp] Cron iniciado\n";

try {
    $processor = new QueueProcessor();
    
    // Executar 2 vezes por minuto (a cada 30 segundos)
    for ($i = 0; $i < 2; $i++) {
        $result = $processor->processAll();
        
        $timestamp = date('Y-m-d H:i:s');
        echo "[$timestamp] Iteração " . ($i + 1) . ": {$result['processed']} itens processados de {$result['campaigns']} campanhas\n";
        
        // Se ainda houver tempo, aguardar 30 segundos
        if ($i < 1) {
            sleep(30);
        }
    }
    
} catch (Exception $e) {
    $timestamp = date('Y-m-d H:i:s');
    echo "[$timestamp] ERRO: " . $e->getMessage() . "\n";
    error_log("Cron Error: " . $e->getMessage());
}

$timestamp = date('Y-m-d H:i:s');
echo "[$timestamp] Cron finalizado\n";
