<?php
// Debug temporário para identificar erro na página de saudações
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/Database.php';

echo "<pre>";
echo "=== DEBUG SAUDAÇÕES ===\n\n";

try {
    $db = Database::getInstance()->getConnection();
    echo "✅ Conexão com banco OK\n\n";
    
    // Verificar se tabela existe
    $result = $db->query("SHOW TABLES LIKE 'user_greetings'");
    if ($result->rowCount() > 0) {
        echo "✅ Tabela user_greetings existe\n";
    } else {
        echo "❌ Tabela user_greetings NÃO existe!\n";
        echo "   Execute: https://zap.dantetesta.com.br/migrations/run_004.php\n";
    }
    
    $result2 = $db->query("SHOW TABLES LIKE 'campaign_greeting_index'");
    if ($result2->rowCount() > 0) {
        echo "✅ Tabela campaign_greeting_index existe\n";
    } else {
        echo "❌ Tabela campaign_greeting_index NÃO existe!\n";
    }
    
    echo "\n=== Testando Model ===\n";
    require_once __DIR__ . '/models/Greeting.php';
    $model = new Greeting();
    echo "✅ Model Greeting carregado\n";
    
    echo "\n=== Testando Controller ===\n";
    require_once __DIR__ . '/core/Controller.php';
    require_once __DIR__ . '/controllers/GreetingController.php';
    echo "✅ Controller carregado\n";
    
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
    echo "Arquivo: " . $e->getFile() . "\n";
    echo "Linha: " . $e->getLine() . "\n";
}

echo "</pre>";
