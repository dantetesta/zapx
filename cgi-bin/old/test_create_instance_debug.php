<?php
/**
 * Script de Debug - Criação de Instância WhatsApp
 * Autor: Dante Testa
 * Data: 27/01/2025
 */

// Carregar configurações
require_once 'config/config.php';
require_once 'config/Database.php';
require_once 'models/User.php';

session_start();

echo "<h1>🔍 Debug - Criação de Instância WhatsApp</h1>";
echo "<hr>";

// 1. Verificar se usuário está logado
echo "<h2>1. Verificação de Sessão</h2>";
if (isset($_SESSION['user_id'])) {
    echo "✅ Usuário logado: ID = " . $_SESSION['user_id'] . "<br>";
    echo "Nome: " . ($_SESSION['user_name'] ?? 'N/A') . "<br>";
    echo "Email: " . ($_SESSION['user_email'] ?? 'N/A') . "<br>";
} else {
    echo "❌ Nenhum usuário logado na sessão<br>";
    echo "<strong>SOLUÇÃO:</strong> Faça login primeiro em: <a href='/auth/login'>/auth/login</a><br>";
    exit;
}

// 2. Verificar dados do usuário no banco
echo "<hr><h2>2. Dados do Usuário no Banco</h2>";
$userModel = new User();
$userData = $userModel->findById($_SESSION['user_id']);

if ($userData) {
    echo "✅ Usuário encontrado no banco<br>";
    echo "<pre>";
    echo "ID: " . $userData['id'] . "\n";
    echo "Nome: " . $userData['name'] . "\n";
    echo "Email: " . $userData['email'] . "\n";
    echo "Admin: " . ($userData['is_admin'] ? 'Sim' : 'Não') . "\n";
    echo "\n<strong>Configuração Evolution API:</strong>\n";
    echo "Instância: " . ($userData['evolution_instance'] ?? 'NULL') . "\n";
    echo "Telefone: " . ($userData['evolution_phone_number'] ?? 'NULL') . "\n";
    echo "Token: " . ($userData['evolution_instance_token'] ? substr($userData['evolution_instance_token'], 0, 20) . '...' : 'NULL') . "\n";
    echo "Status: " . ($userData['evolution_status'] ?? 'NULL') . "\n";
    echo "Criado em: " . ($userData['evolution_created_at'] ?? 'NULL') . "\n";
    echo "</pre>";
} else {
    echo "❌ Usuário não encontrado no banco!<br>";
    exit;
}

// 3. Verificar configurações da Evolution API
echo "<hr><h2>3. Configurações da Evolution API</h2>";
if (defined('EVOLUTION_API_URL') && defined('EVOLUTION_API_KEY')) {
    echo "✅ Constantes definidas<br>";
    echo "URL: " . EVOLUTION_API_URL . "<br>";
    echo "API Key: " . substr(EVOLUTION_API_KEY, 0, 20) . "..." . substr(EVOLUTION_API_KEY, -10) . "<br>";
} else {
    echo "❌ Constantes não definidas em config.php<br>";
    exit;
}

// 4. Testar conexão com Evolution API
echo "<hr><h2>4. Teste de Conexão com Evolution API</h2>";

$testUrl = EVOLUTION_API_URL . '/instance/fetchInstances?instanceName=test';
$ch = curl_init($testUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'apikey: ' . EVOLUTION_API_KEY
]);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "HTTP Code: <strong>" . $http_code . "</strong><br>";

if ($http_code >= 200 && $http_code < 300) {
    echo "✅ Conexão com Evolution API OK<br>";
    echo "Resposta: <pre>" . json_encode(json_decode($response, true), JSON_PRETTY_PRINT) . "</pre>";
} else {
    echo "❌ Erro na conexão com Evolution API<br>";
    echo "Erro cURL: " . ($error ?: 'Nenhum') . "<br>";
    echo "Resposta: <pre>" . $response . "</pre>";
}

// 5. Verificar se usuário já tem instância
echo "<hr><h2>5. Status da Instância Atual</h2>";
if (!empty($userData['evolution_instance'])) {
    echo "⚠️ Usuário JÁ possui uma instância<br>";
    echo "Nome: <strong>" . $userData['evolution_instance'] . "</strong><br>";
    echo "Status: <strong>" . ($userData['evolution_status'] ?? 'unknown') . "</strong><br>";
    echo "<br><strong>AÇÃO:</strong> Delete a instância atual antes de criar uma nova<br>";
    echo "<a href='/whatsapp/conectar' style='padding: 10px 20px; background: #ef4444; color: white; text-decoration: none; border-radius: 5px;'>Ir para Conectar WhatsApp</a>";
} else {
    echo "✅ Usuário NÃO possui instância<br>";
    echo "Pode criar uma nova instância<br>";
}

// 6. Simular criação de instância (SEM executar)
echo "<hr><h2>6. Simulação de Criação de Instância</h2>";

$ddi = '55';
$phoneNumber = '11945531556';
$uniqueName = 'zapx_' . $userData['id'] . '_' . substr(md5(uniqid() . time()), 0, 8);

echo "Dados que seriam enviados:<br>";
echo "<pre>";
echo "instanceName: " . $uniqueName . "\n";
echo "integration: WHATSAPP-BAILEYS\n";
echo "qrcode: true\n";
echo "number: " . $ddi . $phoneNumber . "\n";
echo "</pre>";

echo "<strong>Endpoint:</strong> POST " . EVOLUTION_API_URL . "/instance/create<br>";

// 7. Formulário de teste real
echo "<hr><h2>7. Teste Real de Criação</h2>";
echo "<form method='POST' action='/whatsapp/createInstance'>";
echo "<input type='hidden' name='ddi' value='55'>";
echo "<input type='text' name='phone_number' value='11945531556' style='padding: 10px; border: 1px solid #ccc; border-radius: 5px;'>";
echo "<button type='submit' style='padding: 10px 20px; background: #10b981; color: white; border: none; border-radius: 5px; cursor: pointer; margin-left: 10px;'>Criar Instância (REAL)</button>";
echo "</form>";

echo "<hr>";
echo "<p><strong>Logs do servidor:</strong> Verifique os logs de erro do PHP para mais detalhes</p>";
echo "<p><strong>Arquivo de log:</strong> /var/log/apache2/error.log ou /var/log/php-fpm/error.log</p>";
?>
