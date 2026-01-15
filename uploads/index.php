<?php
/**
 * Proteção de Diretório - uploads/
 * Autor: Dante Testa (https://dantetesta.com.br)
 * Data: 2025-01-28 08:33:00
 * 
 * 🔒 SEGURANÇA: Bloqueia acesso direto ao diretório
 */

// Redirecionar para home
header('Location: ../');
exit;
