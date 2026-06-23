<?php
// ULTRA SIMPLE TEST - Just echoes back what we receive
header('Content-Type: application/json');

echo json_encode([
    'success' => true,
    'message' => 'Test successful - PHP is working',
    'received' => $_POST ? 'POST data received' : 'No POST data',
    'method' => $_SERVER['REQUEST_METHOD'],
    'timestamp' => date('Y-m-d H:i:s')
]);
?>
