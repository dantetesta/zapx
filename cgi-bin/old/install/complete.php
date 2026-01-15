<?php
/**
 * Finalizar Instalação e Redirecionar para Home
 * 
 * @author Dante Testa - https://dantetesta.com.br
 * @date 2025-10-26 07:52:00
 * @version 1.0.0
 */

// Iniciar sessão se ainda não foi iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Destruir completamente a sessão do instalador
$_SESSION = [];

// Destruir cookie de sessão
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destruir sessão
session_destroy();

// Redirecionar para a home/landing page
header('Location: ../home/index');
exit;
