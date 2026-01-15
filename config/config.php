<?php
/**
 * Configurações do Sistema ZAPX
 * Gerado automaticamente pelo instalador
 * Data: 2025-10-31 00:21:02
 */

// Configurações do Banco de Dados
define('DB_HOST', '187.33.241.61');
define('DB_NAME', 'dantetesta_zapx');
define('DB_USER', 'dantetesta_zapx');
define('DB_PASS', 'dantetesta_zapx');
define('DB_CHARSET', 'utf8mb4');

// Configurações da Aplicação
define('APP_NAME', 'ZAPX - Disparo em Massa WhatsApp');
define('APP_URL', 'https://zap.dantetesta.com.br');

// Versão do sistema (centralizada em branding.php)
// NÃO altere aqui, altere em config/branding.php
if (!defined('SYSTEM_VERSION')) {
    require_once __DIR__ . '/branding.php';
}
define('APP_VERSION', SYSTEM_VERSION);

// Configurações de Sessão
define('SESSION_NAME', 'ZAPX_SESSION');
define('SESSION_LIFETIME', 7200);

// Configurações de Upload
// Limites baseados no WhatsApp Business API
define('UPLOAD_MAX_SIZE_IMAGE', 5 * 1024 * 1024);    // 5MB para imagens
define('UPLOAD_MAX_SIZE_VIDEO', 16 * 1024 * 1024);   // 16MB para vídeos
define('UPLOAD_MAX_SIZE_AUDIO', 16 * 1024 * 1024);   // 16MB para áudios
define('UPLOAD_MAX_SIZE_DOCUMENT', 16 * 1024 * 1024); // 16MB para documentos
define('ALLOWED_CSV_EXTENSIONS', ['csv', 'txt']);

// Configurações de Disparo
define('DISPATCH_MIN_INTERVAL', 3);
define('DISPATCH_MAX_INTERVAL', 20);

// Configurações de Segurança - Google reCAPTCHA v2
define('RECAPTCHA_SITE_KEY', '6LfB__krAAAAAPYRcO_H-jZJYdA4OEybjoEue3GM');
define('RECAPTCHA_SECRET_KEY', '6LfB__krAAAAAJR11cXR3mHPY7gqiRboKQXCGVGC');
define('RECAPTCHA_ENABLED', true);

// Configurações da Evolution API
define('EVOLUTION_API_URL', 'https://dante01-evolution-api.y0hzq4.easypanel.host');
define('EVOLUTION_API_KEY', '429683C4C977415CAAFCCE10F7D57E11');

// Configurações de Email (SMTP)
define('SMTP_HOST', 'mail.dantetesta.com.br');
define('SMTP_PORT', 465);
define('SMTP_ENCRYPTION', 'ssl'); // tls, ssl ou none
define('SMTP_USER', 'no-reply@dantetesta.com.br');
define('SMTP_PASS', 'ddtevy11@');
define('SMTP_FROM', 'no-reply@dantetesta.com.br');
define('SMTP_FROM_NAME', 'ZAPX');

// Timezone
date_default_timezone_set('America/Sao_Paulo');

// Iniciar sessão
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}
