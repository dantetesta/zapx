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
// 🔒 SEGURANÇA: NUNCA use 'root' sem senha em produção!
// Configure credenciais seguras durante a instalação
define('DB_HOST', 'localhost');
define('DB_NAME', 'zapx_system');
define('DB_USER', 'zapx_user');  // ⚠️ ALTERE para um usuário específico
define('DB_PASS', '');           // ⚠️ DEFINA uma senha forte durante instalação
define('DB_CHARSET', 'utf8mb4');

// Configurações da Aplicação
define('APP_NAME', 'ZAPX - Disparo em Massa WhatsApp');
define('APP_URL', 'http://localhost/');  // Altere conforme seu ambiente

// Versão do sistema (centralizada em branding.php)
// NÃO altere aqui, altere em config/branding.php
if (!defined('SYSTEM_VERSION')) {
    require_once __DIR__ . '/branding.php';
}
define('APP_VERSION', SYSTEM_VERSION);

// Configurações de Sessão
define('SESSION_NAME', 'ZAPX_SESSION');
define('SESSION_LIFETIME', 7200); // 2 horas em segundos (inatividade normal)
define('SESSION_DISPATCH_LIFETIME', 316800); // 88 horas (3 dias + 16 horas) para disparos em massa
// Cálculo: 15.000 mensagens × 20 segundos = 83,3 horas
// Margem de segurança: +5 horas = 88,3 horas (arredondado para 88h)

// Configurações de Upload
// Limites baseados no WhatsApp Business API
define('UPLOAD_MAX_SIZE_IMAGE', 5 * 1024 * 1024);    // 5MB para imagens
define('UPLOAD_MAX_SIZE_VIDEO', 16 * 1024 * 1024);   // 16MB para vídeos
define('UPLOAD_MAX_SIZE_AUDIO', 16 * 1024 * 1024);   // 16MB para áudios
define('UPLOAD_MAX_SIZE_DOCUMENT', 16 * 1024 * 1024); // 16MB para documentos
define('ALLOWED_CSV_EXTENSIONS', ['csv', 'txt']);

// Configurações de Disparo
define('DISPATCH_MIN_INTERVAL', 3);  // Intervalo mínimo em segundos (evita encavalamentos)
define('DISPATCH_MAX_INTERVAL', 20); // Intervalo máximo em segundos (maior randomicidade)

// Configurações de Segurança - Google reCAPTCHA v2
// Obtenha suas chaves em: https://www.google.com/recaptcha/admin
define('RECAPTCHA_SITE_KEY', '');    // Chave do Site (pública)
define('RECAPTCHA_SECRET_KEY', '');  // Chave Secreta (privada)
define('RECAPTCHA_ENABLED', false);  // true = ativar, false = desativar

// Timezone
date_default_timezone_set('America/Sao_Paulo');

// Configurações de Erro
// Em PRODUÇÃO, altere para 0
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 🔒 IMPORTANTE: Timeout de execução PHP
// Para disparos em massa longos, o timeout precisa ser alto
// Padrão: 30 segundos (muito baixo para disparos)
// Recomendado: 0 (sem limite) ou valor alto
ini_set('max_execution_time', '0'); // 0 = sem limite (recomendado para disparos)
ini_set('max_input_time', '0'); // 0 = sem limite para receber dados

// Iniciar sessão
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
    
    // 🔒 SEGURANÇA: Timeout de sessão por inatividade
    if (isset($_SESSION['LAST_ACTIVITY'])) {
        $inactive = time() - $_SESSION['LAST_ACTIVITY'];
        
        // Verificar se está em disparo em massa (timeout estendido)
        $isDispatching = isset($_SESSION['DISPATCHING']) && $_SESSION['DISPATCHING'] === true;
        $timeout = $isDispatching ? SESSION_DISPATCH_LIFETIME : SESSION_LIFETIME;
        
        // Se inativo por mais do que o timeout, destruir sessão
        if ($inactive > $timeout) {
            session_unset();
            session_destroy();
            session_start();
            $_SESSION['timeout_message'] = 'Sua sessão expirou por inatividade.';
        }
    }
    
    // Atualizar timestamp de última atividade
    $_SESSION['LAST_ACTIVITY'] = time();
}
