<?php
require_once '../config/database.php';
require_once '../includes/Utils.php';
require_once 'middleware/verify_api_key.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Utils::sendJsonResponse(['error' => 'Method not allowed'], 405);
}

// Verify API key
$client = verifyApiKey();

// Check for auth token
$headers = getallheaders();
$authHeader = null;

// Check for different possible header cases
foreach ($headers as $key => $value) {
    if (strtolower($key) === 'authorization') {
        $authHeader = $value;
        break;
    }
}

if (!$authHeader || !preg_match('/^Bearer\s+(.+)$/i', $authHeader, $matches)) {
    Utils::sendJsonResponse(['error' => 'Authentication required'], 401);
}

$token = trim($matches[1]);

try {
    // Begin transaction
    $pdo->beginTransaction();

    // Verify token and get user info
    $stmt = $pdo->prepare("
        SELECT 
            at.user_id,
            at.expires_at,
            au.dev_id
        FROM auth_tokens at
        JOIN api_users au ON au.id = at.user_id
        WHERE at.token = ? AND au.dev_id = ?
        ORDER BY at.created_at DESC
        LIMIT 1
    ");
    
    $stmt->execute([$token, $client['dev_id']]);
    $tokenData = $stmt->fetch();

    if (!$tokenData || strtotime($tokenData['expires_at']) < time()) {
        $pdo->rollBack();
        Utils::sendJsonResponse(['error' => 'Invalid or expired token'], 401);
    }

    // Generate new auth token
    $newToken = bin2hex(random_bytes(32));
    $tokenExpiresAt = date('Y-m-d H:i:s', strtotime('+30 minutes'));

    // Update the token
    $stmt = $pdo->prepare("
        UPDATE auth_tokens 
        SET token = ?, expires_at = ? 
        WHERE user_id = ? AND token = ?
    ");
    $stmt->execute([$newToken, $tokenExpiresAt, $tokenData['user_id'], $token]);

    // Commit transaction
    $pdo->commit();

    Utils::sendJsonResponse([
        'success' => true,
        'message' => 'Token refreshed successfully',
        'data' => [
            'auth_token' => $newToken,
            'expires_at' => $tokenExpiresAt
        ]
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log($e->getMessage());
    Utils::sendJsonResponse(['error' => 'Failed to refresh token. Please try again.'], 500);
} 