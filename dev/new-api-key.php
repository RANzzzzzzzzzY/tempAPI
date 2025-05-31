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

$systemName = Utils::sanitizeInput($data['system_name'] ?? '');

if (!$systemName) {
    Utils::sendJsonResponse(['error' => 'System name is required'], 400);
}

try {
    // Generate new API key
    $apiKey = Utils::generateApiKey();

    // Create new API client
    $stmt = $pdo->prepare("
        INSERT INTO api_clients (dev_id, system_name, api_key)
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$devId, $systemName, $apiKey]);

    Utils::sendJsonResponse([
        'message' => 'API key generated successfully',
        'api_key' => $apiKey
    ], 201);

} catch (Exception $e) {
    error_log($e->getMessage());
    Utils::sendJsonResponse(['error' => 'Failed to generate API key'], 500);
} 