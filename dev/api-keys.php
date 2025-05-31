<?php
require_once '../config/database.php';
require_once '../includes/Utils.php';

header('Content-Type: application/json');

// Check for auth token
$headers = getallheaders();
$token = $headers['Authorization'] ?? '';

if (!$token || !str_starts_with($token, 'Bearer ')) {
    Utils::sendJsonResponse(['error' => 'Unauthorized'], 401);
}

$token = substr($token, 7);

// Verify JWT token
if (!Utils::verifyJWT($token, getenv('JWT_SECRET') ?: 'your-secret-key')) {
    Utils::sendJsonResponse(['error' => 'Invalid token'], 401);
}

// Get developer ID from token
$payload = json_decode(base64_decode(explode('.', $token)[1]), true);
$devId = $payload['dev_id'] ?? null;

if (!$devId) {
    Utils::sendJsonResponse(['error' => 'Invalid token'], 401);
}

try {
    // Get API keys for developer
    $stmt = $pdo->prepare("
        SELECT id, system_name, api_key 
        FROM api_clients 
        WHERE dev_id = ? AND is_active = TRUE
        ORDER BY created_at DESC
    ");
    $stmt->execute([$devId]);
    $apiClients = $stmt->fetchAll();

    Utils::sendJsonResponse(['api_clients' => $apiClients]);

} catch (Exception $e) {
    error_log($e->getMessage());
    Utils::sendJsonResponse(['error' => 'Failed to fetch API keys'], 500);
} 