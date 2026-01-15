<?php
/**
 * Configurações do Sistema ZAPX - EXEMPLO
 * Autor: Dante Testa (https://dantetesta.com.br)
 * Data: 2025-10-25 07:13:00
 * 
 * INSTRUÇÕES:
 * 1. Copie este arquivo para config.php
 * 2. Preencha com suas credenciais
 * 3. Nunca commite o arquivo config.php no Git
 */

// Configurações do Banco de Dados
define('DB_HOST', 'localhost');
define('DB_NAME', 'zapx_system');
define('DB_USER', 'root');  // Altere para seu usuário MySQL
define('DB_PASS', '');      // Altere para sua senha MySQL
define('DB_CHARSET', 'utf8mb4');

// Configurações da Aplicação
define('APP_NAME', 'ZAPX - Disparo em Massa WhatsApp');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/');  // Altere conforme seu ambiente

// Configurações de Sessão
define('SESSION_NAME', 'ZAPX_SESSION');
define('SESSION_LIFETIME', 7200); // 2 horas em segundos

// Configurações de Upload
define('UPLOAD_MAX_SIZE', 5242880); // 5MB em bytes
define('ALLOWED_CSV_EXTENSIONS', ['csv', 'txt']);

// Configurações de Disparo
define('DISPATCH_MIN_INTERVAL', 2);  // Intervalo mínimo em segundos
define('DISPATCH_MAX_INTERVAL', 10); // Intervalo máximo em segundos

// Timezone
date_default_timezone_set('America/Sao_Paulo');

// Configurações de Erro
// Em PRODUÇÃO, altere para 0
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar sessão
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}
