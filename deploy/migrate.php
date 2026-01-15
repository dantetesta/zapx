<?php
/**
 * Script de Migrações via Web - ZAPX
 * Autor: Dante Testa (https://dantetesta.com.br)
 * Data: 2026-01-14 20:35:00
 * 
 * USO: Acesse via navegador com token de segurança
 * URL: https://zap.dantetesta.com.br/deploy/migrate.php?token=SEU_TOKEN
 */

// Token de segurança (altere para um valor único e seguro!)
define('MIGRATE_TOKEN', 'zapx_migrate_2026_secure_token');

// Verificar token
if (!isset($_GET['token']) || $_GET['token'] !== MIGRATE_TOKEN) {
    http_response_code(403);
    die('Acesso negado. Token inválido.');
}

// Carregar configurações do sistema
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/Database.php';

header('Content-Type: text/html; charset=utf-8');

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZAPX - Migrações</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white min-h-screen p-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold text-purple-400 mb-8">🚀 ZAPX - Sistema de Migrações</h1>
        
        <div class="bg-gray-800 rounded-lg p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">📊 Status do Sistema</h2>
            <ul class="space-y-2">
                <li>✅ <strong>Host:</strong> <?= DB_HOST ?></li>
                <li>✅ <strong>Database:</strong> <?= DB_NAME ?></li>
                <li>✅ <strong>Versão:</strong> <?= APP_VERSION ?? 'N/A' ?></li>
            </ul>
        </div>

<?php

try {
    $db = Database::getInstance()->getConnection();
    
    echo '<div class="bg-green-900/50 rounded-lg p-4 mb-6">';
    echo '✅ <strong>Conexão com banco de dados estabelecida!</strong>';
    echo '</div>';
    
    // Criar tabela de migrações se não existir
    $db->exec("
        CREATE TABLE IF NOT EXISTS migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL UNIQUE,
            batch INT NOT NULL DEFAULT 1,
            executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_migration (migration),
            INDEX idx_batch (batch)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Obter próximo batch
    $stmt = $db->query("SELECT COALESCE(MAX(batch), 0) + 1 as next_batch FROM migrations");
    $batch = $stmt->fetch()['next_batch'];
    
    echo '<div class="bg-gray-800 rounded-lg p-6 mb-6">';
    echo '<h2 class="text-xl font-semibold mb-4">📁 Processando Migrações (Batch #' . $batch . ')</h2>';
    echo '<div class="space-y-2">';
    
    $migrationsDir = __DIR__ . '/migrations';
    $migrated = 0;
    $skipped = 0;
    
    if (is_dir($migrationsDir)) {
        $files = glob($migrationsDir . '/*.sql');
        sort($files);
        
        foreach ($files as $file) {
            $migrationName = basename($file);
            
            // Verificar se já foi executada
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM migrations WHERE migration = ?");
            $stmt->execute([$migrationName]);
            $exists = $stmt->fetch()['count'] > 0;
            
            if (!$exists) {
                // Executar migração
                $sql = file_get_contents($file);
                
                try {
                    $db->exec($sql);
                    
                    // Registrar migração
                    $stmt = $db->prepare("INSERT INTO migrations (migration, batch) VALUES (?, ?)");
                    $stmt->execute([$migrationName, $batch]);
                    
                    echo '<div class="text-green-400">✅ Executada: ' . htmlspecialchars($migrationName) . '</div>';
                    $migrated++;
                } catch (PDOException $e) {
                    echo '<div class="text-red-400">❌ Erro em ' . htmlspecialchars($migrationName) . ': ' . htmlspecialchars($e->getMessage()) . '</div>';
                }
            } else {
                echo '<div class="text-yellow-400">⏭️ Ignorada: ' . htmlspecialchars($migrationName) . ' (já executada)</div>';
                $skipped++;
            }
        }
    } else {
        echo '<div class="text-yellow-400">⚠️ Diretório de migrações não encontrado</div>';
    }
    
    echo '</div></div>';
    
    // Seeds (se solicitado)
    if (isset($_GET['seed']) && $_GET['seed'] === '1') {
        echo '<div class="bg-gray-800 rounded-lg p-6 mb-6">';
        echo '<h2 class="text-xl font-semibold mb-4">🌱 Processando Seeds</h2>';
        echo '<div class="space-y-2">';
        
        $seedsDir = __DIR__ . '/seeds';
        $seeded = 0;
        
        if (is_dir($seedsDir)) {
            $files = glob($seedsDir . '/*.sql');
            sort($files);
            
            foreach ($files as $file) {
                $seedName = basename($file);
                $sql = file_get_contents($file);
                
                try {
                    $db->exec($sql);
                    echo '<div class="text-green-400">✅ Seed executado: ' . htmlspecialchars($seedName) . '</div>';
                    $seeded++;
                } catch (PDOException $e) {
                    echo '<div class="text-yellow-400">⚠️ ' . htmlspecialchars($seedName) . ': ' . htmlspecialchars($e->getMessage()) . '</div>';
                }
            }
        }
        
        echo '</div></div>';
    }
    
    // Resumo
    echo '<div class="bg-purple-900/50 rounded-lg p-6">';
    echo '<h2 class="text-xl font-semibold mb-4">📋 Resumo</h2>';
    echo '<ul class="space-y-1">';
    echo '<li>✅ Migrações executadas: <strong>' . $migrated . '</strong></li>';
    echo '<li>⏭️ Migrações ignoradas: <strong>' . $skipped . '</strong></li>';
    if (isset($seeded)) {
        echo '<li>🌱 Seeds executados: <strong>' . $seeded . '</strong></li>';
    }
    echo '</ul>';
    echo '</div>';
    
    // Link para seeds
    if (!isset($_GET['seed'])) {
        $seedUrl = '?token=' . MIGRATE_TOKEN . '&seed=1';
        echo '<div class="mt-6">';
        echo '<a href="' . $seedUrl . '" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg inline-block">';
        echo '🌱 Executar Seeds Também';
        echo '</a>';
        echo '</div>';
    }
    
} catch (PDOException $e) {
    echo '<div class="bg-red-900/50 rounded-lg p-4">';
    echo '❌ <strong>Erro de conexão:</strong> ' . htmlspecialchars($e->getMessage());
    echo '</div>';
}

?>

        <div class="mt-8 text-gray-500 text-sm">
            <p>Autor: <a href="https://dantetesta.com.br" class="text-purple-400 hover:underline">Dante Testa</a></p>
            <p>Data: 2026-01-14 20:35:00</p>
        </div>
    </div>
</body>
</html>
