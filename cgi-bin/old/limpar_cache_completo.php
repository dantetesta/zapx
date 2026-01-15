<?php
/**
 * Script para limpar TODOS os tipos de cache
 * Autor: Dante Testa (https://dantetesta.com.br)
 * Data: 2025-10-25 23:40:00
 */

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'>";
echo "<meta http-equiv='Cache-Control' content='no-cache, no-store, must-revalidate'>";
echo "<meta http-equiv='Pragma' content='no-cache'>";
echo "<meta http-equiv='Expires' content='0'>";
echo "<title>Limpar Cache Completo - ZAPX</title>";
echo "<style>body{font-family:Arial,sans-serif;padding:40px;background:#f5f5f5;}";
echo ".container{max-width:800px;margin:0 auto;background:white;padding:30px;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,0.1);}";
echo ".success{background:#d4edda;color:#155724;padding:15px;border-radius:5px;margin:10px 0;}";
echo ".info{background:#cce5ff;color:#004085;padding:15px;border-radius:5px;margin:10px 0;}";
echo ".btn{background:#007bff;color:white;padding:15px 30px;border:none;border-radius:5px;cursor:pointer;text-decoration:none;display:inline-block;margin:10px 5px;}";
echo ".btn:hover{background:#0056b3;}";
echo "</style></head><body>";

echo "<div class='container'>";
echo "<h1>🧹 Limpar Cache Completo - ZAPX</h1>";

$cacheCleared = [];

// 1. Limpar OPcache (PHP)
if (function_exists('opcache_reset')) {
    opcache_reset();
    $cacheCleared[] = "✅ OPcache PHP limpo";
} else {
    $cacheCleared[] = "⚠️ OPcache não está ativo";
}

// 2. Limpar cache de arquivos
if (function_exists('clearstatcache')) {
    clearstatcache();
    $cacheCleared[] = "✅ Cache de arquivos PHP limpo";
}

// 3. Forçar reload de todos os arquivos PHP
$phpFiles = [];
$phpFiles = array_merge($phpFiles, glob(__DIR__ . '/controllers/*.php'));
$phpFiles = array_merge($phpFiles, glob(__DIR__ . '/models/*.php'));
$phpFiles = array_merge($phpFiles, glob(__DIR__ . '/views/**/*.php'));
$phpFiles = array_merge($phpFiles, glob(__DIR__ . '/core/*.php'));
$phpFiles = array_merge($phpFiles, glob(__DIR__ . '/*.php'));

$touchedFiles = 0;
foreach ($phpFiles as $file) {
    if (file_exists($file)) {
        touch($file);
        $touchedFiles++;
    }
}
$cacheCleared[] = "✅ $touchedFiles arquivos PHP atualizados (forçar reload)";

// 4. Limpar cache de sessão (se necessário)
if (session_status() === PHP_SESSION_ACTIVE) {
    session_regenerate_id(true);
    $cacheCleared[] = "✅ ID de sessão regenerado";
}

// 5. Headers anti-cache para esta página
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

echo "<h2>🎯 Cache do Servidor Limpo:</h2>";
foreach ($cacheCleared as $item) {
    echo "<div class='success'>$item</div>";
}

echo "<h2>🌐 Limpar Cache do Navegador:</h2>";
echo "<div class='info'>";
echo "<p><strong>Para limpar o cache do navegador:</strong></p>";
echo "<ul>";
echo "<li><strong>Chrome/Edge:</strong> CTRL + SHIFT + R (Windows) ou CMD + SHIFT + R (Mac)</li>";
echo "<li><strong>Firefox:</strong> CTRL + F5 (Windows) ou CMD + SHIFT + R (Mac)</li>";
echo "<li><strong>Safari:</strong> CMD + OPTION + R</li>";
echo "<li><strong>Ou:</strong> Abra uma aba anônima/privada</li>";
echo "</ul>";
echo "</div>";

echo "<h2>🚀 Testar Sistema:</h2>";
echo "<a href='/dashboard/index?v=" . time() . "' class='btn'>📊 Dashboard</a>";
echo "<a href='/whatsapp/conectar?v=" . time() . "' class='btn'>📱 Conectar WhatsApp</a>";
echo "<a href='/contacts/index?v=" . time() . "' class='btn'>👥 Contatos</a>";
echo "<a href='/dispatch/index?v=" . time() . "' class='btn'>📤 Disparar</a>";

echo "<h2>🔄 Forçar Reload Automático:</h2>";
echo "<button class='btn' onclick='forcarReload()'>🔄 Forçar Reload desta Página</button>";
echo "<button class='btn' onclick='limparTudo()'>🧹 Limpar Tudo + Reload</button>";

echo "<script>";
echo "function forcarReload() {";
echo "  window.location.href = window.location.pathname + '?v=' + Date.now();";
echo "}";
echo "function limparTudo() {";
echo "  // Limpar localStorage";
echo "  if (typeof(Storage) !== 'undefined') {";
echo "    localStorage.clear();";
echo "    sessionStorage.clear();";
echo "  }";
echo "  // Forçar reload sem cache";
echo "  window.location.reload(true);";
echo "}";
echo "</script>";

echo "<hr style='margin: 30px 0;'>";
echo "<p style='text-align: center; color: #666; font-size: 12px;'>";
echo "Script executado em: " . date('Y-m-d H:i:s') . "<br>";
echo "Timestamp: " . time() . "<br>";
echo "Desenvolvido por <a href='https://dantetesta.com.br' target='_blank'>Dante Testa</a>";
echo "</p>";

echo "</div></body></html>";
