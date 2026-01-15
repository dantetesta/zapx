<?php
/**
 * Testes Unitários - Sistema de Campanhas
 * Autor: Dante Testa (https://dantetesta.com.br)
 * Data: 2026-01-14 23:30:00
 * 
 * Executar via CLI: php tests/test_campaign.php
 */

define('BASE_PATH', dirname(__DIR__));

// Carregar configurações
require_once BASE_PATH . '/config/config.php';
require_once BASE_PATH . '/config/Database.php';
require_once BASE_PATH . '/models/Campaign.php';
require_once BASE_PATH . '/models/Queue.php';
require_once BASE_PATH . '/models/User.php';
require_once BASE_PATH . '/models/Contact.php';

echo "╔════════════════════════════════════════════╗\n";
echo "║  ZAPX - Testes do Sistema de Campanhas     ║\n";
echo "╚════════════════════════════════════════════╝\n\n";

$passed = 0;
$failed = 0;

function test($name, $condition) {
    global $passed, $failed;
    if ($condition) {
        echo "✅ PASS: $name\n";
        $passed++;
    } else {
        echo "❌ FAIL: $name\n";
        $failed++;
    }
}

// ==========================================
// TESTE 1: Conexão com banco de dados
// ==========================================
echo "\n📦 TESTE 1: Conexão com Banco de Dados\n";
echo str_repeat("-", 50) . "\n";

try {
    $db = Database::getInstance()->getConnection();
    test("Conexão com MySQL", $db !== null);
} catch (Exception $e) {
    test("Conexão com MySQL", false);
    echo "   Erro: " . $e->getMessage() . "\n";
}

// ==========================================
// TESTE 2: Tabelas existem
// ==========================================
echo "\n📦 TESTE 2: Tabelas do Sistema\n";
echo str_repeat("-", 50) . "\n";

$tables = ['dispatch_campaigns', 'dispatch_queue', 'dispatch_locks'];
foreach ($tables as $table) {
    try {
        $stmt = $db->query("SHOW TABLES LIKE '$table'");
        $exists = $stmt->rowCount() > 0;
        test("Tabela $table existe", $exists);
    } catch (Exception $e) {
        test("Tabela $table existe", false);
    }
}

// ==========================================
// TESTE 3: Models instanciam corretamente
// ==========================================
echo "\n📦 TESTE 3: Models\n";
echo str_repeat("-", 50) . "\n";

try {
    $campaignModel = new Campaign();
    test("Campaign Model instanciado", $campaignModel !== null);
} catch (Exception $e) {
    test("Campaign Model instanciado", false);
}

try {
    $queueModel = new Queue();
    test("Queue Model instanciado", $queueModel !== null);
} catch (Exception $e) {
    test("Queue Model instanciado", false);
}

// ==========================================
// TESTE 4: Operações CRUD de Campaign
// ==========================================
echo "\n📦 TESTE 4: CRUD de Campanhas\n";
echo str_repeat("-", 50) . "\n";

// Buscar um usuário existente para teste
$stmt = $db->query("SELECT id FROM users LIMIT 1");
$testUser = $stmt->fetch();

if ($testUser) {
    $userId = $testUser['id'];
    
    // CREATE
    try {
        $campaignId = $campaignModel->create([
            'user_id' => $userId,
            'name' => 'Campanha de Teste',
            'message' => 'Mensagem de teste {nome}',
            'media_type' => 'text',
            'total_contacts' => 5
        ]);
        test("Criar campanha", $campaignId > 0);
    } catch (Exception $e) {
        test("Criar campanha", false);
        echo "   Erro: " . $e->getMessage() . "\n";
        $campaignId = null;
    }
    
    // READ
    if ($campaignId) {
        try {
            $campaign = $campaignModel->findById($campaignId);
            test("Buscar campanha por ID", $campaign && $campaign['name'] === 'Campanha de Teste');
        } catch (Exception $e) {
            test("Buscar campanha por ID", false);
        }
        
        // UPDATE STATUS
        try {
            $updated = $campaignModel->updateStatus($campaignId, 'running');
            $campaign = $campaignModel->findById($campaignId);
            test("Atualizar status para running", $campaign['status'] === 'running');
        } catch (Exception $e) {
            test("Atualizar status para running", false);
        }
        
        // INCREMENT COUNTER
        try {
            $campaignModel->incrementCounter($campaignId, 'sent_count');
            $campaign = $campaignModel->findById($campaignId);
            test("Incrementar contador sent_count", $campaign['sent_count'] == 1);
        } catch (Exception $e) {
            test("Incrementar contador sent_count", false);
        }
        
        // DELETE (limpeza)
        try {
            // Primeiro mudar status para poder deletar
            $campaignModel->updateStatus($campaignId, 'cancelled');
            $deleted = $campaignModel->delete($campaignId, $userId);
            test("Deletar campanha de teste", $deleted);
        } catch (Exception $e) {
            test("Deletar campanha de teste", false);
        }
    }
} else {
    echo "⚠️  Nenhum usuário encontrado para testes\n";
}

// ==========================================
// TESTE 5: Operações de Queue
// ==========================================
echo "\n📦 TESTE 5: Operações de Fila\n";
echo str_repeat("-", 50) . "\n";

if ($testUser) {
    // Criar campanha temporária para testar fila
    try {
        $tempCampaignId = $campaignModel->create([
            'user_id' => $userId,
            'name' => 'Temp Campaign for Queue Test',
            'message' => 'Test',
            'media_type' => 'text',
            'total_contacts' => 1
        ]);
        
        // Buscar um contato existente
        $stmt = $db->query("SELECT id FROM contacts WHERE user_id = $userId LIMIT 1");
        $testContact = $stmt->fetch();
        
        if ($testContact) {
            // ADD ITEM
            $added = $queueModel->addItem($tempCampaignId, $testContact['id']);
            test("Adicionar item na fila", $added);
            
            // COUNT BY STATUS
            $counts = $queueModel->countByStatus($tempCampaignId);
            test("Contar itens por status", $counts['pending'] == 1);
            
            // HAS PENDING
            $hasPending = $queueModel->hasPending($tempCampaignId);
            test("Verificar pendentes", $hasPending === true);
            
            // GET NEXT ITEM
            $nextItem = $queueModel->getNextItem($tempCampaignId);
            test("Obter próximo item", $nextItem !== false);
            
            // MARK PROCESSING
            if ($nextItem) {
                $queueModel->markProcessing($nextItem['id']);
                $counts = $queueModel->countByStatus($tempCampaignId);
                test("Marcar como processing", $counts['processing'] == 1);
                
                // MARK SENT
                $queueModel->markSent($nextItem['id']);
                $counts = $queueModel->countByStatus($tempCampaignId);
                test("Marcar como sent", $counts['sent'] == 1);
            }
        } else {
            echo "⚠️  Nenhum contato encontrado para testes de fila\n";
        }
        
        // Limpar campanha temporária
        $campaignModel->updateStatus($tempCampaignId, 'cancelled');
        $campaignModel->delete($tempCampaignId, $userId);
        
    } catch (Exception $e) {
        echo "⚠️  Erro nos testes de fila: " . $e->getMessage() . "\n";
    }
}

// ==========================================
// TESTE 6: QueueProcessor
// ==========================================
echo "\n📦 TESTE 6: Queue Processor\n";
echo str_repeat("-", 50) . "\n";

try {
    require_once BASE_PATH . '/core/QueueProcessor.php';
    $processor = new QueueProcessor();
    test("QueueProcessor instanciado", $processor !== null);
} catch (Exception $e) {
    test("QueueProcessor instanciado", false);
    echo "   Erro: " . $e->getMessage() . "\n";
}

// ==========================================
// RESULTADO FINAL
// ==========================================
echo "\n" . str_repeat("=", 50) . "\n";
echo "📊 RESULTADO FINAL\n";
echo str_repeat("=", 50) . "\n";
echo "✅ Passou: $passed\n";
echo "❌ Falhou: $failed\n";
echo "📈 Taxa de sucesso: " . round(($passed / ($passed + $failed)) * 100, 1) . "%\n";

if ($failed === 0) {
    echo "\n🎉 TODOS OS TESTES PASSARAM!\n";
    exit(0);
} else {
    echo "\n⚠️  Alguns testes falharam. Verifique os erros acima.\n";
    exit(1);
}
