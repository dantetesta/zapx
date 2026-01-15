<?php
// Debug para verificar delay na campanha
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/Database.php';

header('Content-Type: application/json');

$db = Database::getInstance()->getConnection();

// Buscar campanhas em execução com info de delay
$sql = "SELECT id, name, status, next_delay, next_send_at, min_interval, max_interval 
        FROM dispatch_campaigns 
        WHERE status = 'running' 
        ORDER BY id DESC LIMIT 5";

$stmt = $db->query($sql);
$campaigns = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Verificar se colunas existem
$columns = $db->query("SHOW COLUMNS FROM dispatch_campaigns LIKE 'next_delay'")->fetchAll();

echo json_encode([
    'column_exists' => count($columns) > 0,
    'campaigns' => $campaigns,
    'server_time' => date('Y-m-d H:i:s')
], JSON_PRETTY_PRINT);
