<?php
/**
 * Configurações do Sistema ZAPX
 * Gerado automaticamente pelo instalador
 * Data: 2025-10-26 08:10:25
 */

// Configurações do Banco de Dados
define('DB_HOST', 'localhost');
define('DB_NAME', 'dantetesta_zapmassa');
define('DB_USER', 'dantetesta_zapmassa');
define('DB_PASS', 'dantetesta_zapmassa');
define('DB_CHARSET', 'utf8mb4');

// Configurações da Aplicação
define('APP_NAME', 'ZAPX - Disparo em Massa WhatsApp');
define('APP_VERSION', '3.0.0');
define('APP_URL', 'https://zap.dantetesta.com.br');

// Configurações de Sessão
define('SESSION_NAME', 'ZAPX_SESSION');
define('SESSION_LIFETIME', 7200);

// Configurações de Upload
define('UPLOAD_MAX_SIZE', 5242880);
define('ALLOWED_CSV_EXTENSIONS', ['csv', 'txt']);

// Configurações de Disparo
define('DISPATCH_MIN_INTERVAL', 3);
define('DISPATCH_MAX_INTERVAL', 20);

// Configurações da Evolution API
define('EVOLUTION_API_URL', 'http://evo-i0sgwc0kg40g00wo0gsw8ccs.159.65.250.205.sslip.io');
define('EVOLUTION_API_KEY', 'GIOTv3GiTO68CCigiGs0y6EWX1L3lDgD');

// Configurações de Email (SMTP)
define('SMTP_HOST', 'mail.dantetesta.com.br');
define('SMTP_PORT', 587);
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
