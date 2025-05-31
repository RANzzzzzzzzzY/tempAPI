<?php
require_once '../config/database.php';
require_once '../includes/Utils.php';

header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Utils::sendJsonResponse(['error' => 'Method not allowed'], 405);
}

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

// Get and validate input
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    Utils::sendJsonResponse(['error' => 'Invalid JSON data'], 400);
}

$clientId = filter_var($data['client_id'] ?? null, FILTER_VALIDATE_INT);

if (!$clientId) {
    Utils::sendJsonResponse(['error' => 'Client ID is required'], 400);
}

try {
    // Verify ownership of API client
    $stmt = $pdo->prepare("SELECT id FROM api_clients WHERE id = ? AND dev_id = ?");
    $stmt->execute([$clientId, $devId]);
    
    if (!$stmt->fetch()) {
        Utils::sendJsonResponse(['error' => 'API client not found'], 404);
    }

    // Generate new API key
    $newApiKey = Utils::generateApiKey();

    // Update API client
    $stmt = $pdo->prepare("UPDATE api_clients SET api_key = ? WHERE id = ?");
    $stmt->execute([$newApiKey, $clientId]);

    Utils::sendJsonResponse([
        'message' => 'API key regenerated successfully',
        'api_key' => $newApiKey
    ]);

} catch (Exception $e) {
    error_log($e->getMessage());
    Utils::sendJsonResponse(['error' => 'Failed to regenerate API key'], 500);
} 