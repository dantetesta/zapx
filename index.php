<?php
/**
 * Front Controller - ZAPX System
 * Autor: Dante Testa (https://dantetesta.com.br)
 * Data: 2025-10-25 07:13:00
 * Versão: 1.0.0
 */

// Iniciar sessão
session_start();

// Verificar se o sistema está instalado
if (!file_exists(__DIR__ . '/config/installed.lock')) {
    // Redirecionar para o instalador
    header('Location: install/index.php');
    exit;
}

// Carregar configurações
require_once 'config/config.php';
require_once 'config/Database.php';
require_once 'config/branding.php';

// Autoloader para classes
spl_autoload_register(function ($class) {
    $directories = ['controllers', 'models', 'core'];
    
    foreach ($directories as $directory) {
        $file = __DIR__ . '/' . $directory . '/' . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Inicializar aplicação
$app = new App();
