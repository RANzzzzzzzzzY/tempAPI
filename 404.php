<?php
header('HTTP/1.1 404 Not Found');
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept');

echo json_encode([
    'success' => false,
    'error' => 'Resource not found'
]); 