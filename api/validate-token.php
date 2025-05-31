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
    Utils::sendJsonResponse([
        'success' => false,
        'error' => 'Method not allowed',
        'message' => 'This endpoint only accepts POST requests'
    ], 405);
}

try {
    // Verify API key and get client info
    $client = verifyApiKey();

    // Get authorization header
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
        Utils::sendJsonResponse([
            'success' => false,
            'error' => 'Authentication required',
            'message' => 'Authorization header with Bearer token is required'
        ], 401);
    }

    $token = trim($matches[1]);

    // Verify token and get user info
    $stmt = $pdo->prepare("
        SELECT 
            at.id as token_id,
            at.user_id,
            at.token,
            at.expires_at,
            au.email,
            au.is_email_verified,
            au.is_active,
            au.dev_id
        FROM auth_tokens at
        JOIN api_users au ON au.id = at.user_id
        WHERE at.token = ? 
        AND au.dev_id = ?
        ORDER BY at.created_at DESC
        LIMIT 1
    ");
    
    $stmt->execute([$token, $client['dev_id']]);
    $tokenData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$tokenData) {
        Utils::sendJsonResponse([
            'success' => false,
            'error' => 'Invalid token',
            'message' => 'The provided token is invalid or does not exist'
        ], 401);
    }

    // Check if token has expired
    if (strtotime($tokenData['expires_at']) < time()) {
        Utils::sendJsonResponse([
            'success' => false,
            'error' => 'Token expired',
            'message' => 'The authentication token has expired',
            'expired_at' => $tokenData['expires_at']
        ], 401);
    }

    // Check if user is active
    if (!$tokenData['is_active']) {
        Utils::sendJsonResponse([
            'success' => false,
            'error' => 'Account inactive',
            'message' => 'User account is not active'
        ], 403);
    }

    // Return success with user info
    Utils::sendJsonResponse([
        'success' => true,
        'message' => 'Token is valid',
        'data' => [
            'user_id' => $tokenData['user_id'],
            'email' => $tokenData['email'],
            'is_email_verified' => $tokenData['is_email_verified'],
            'expires_at' => $tokenData['expires_at']
        ]
    ]);

} catch (Exception $e) {
    error_log("Token validation error: " . $e->getMessage());
    Utils::sendJsonResponse([
        'success' => false,
        'error' => 'Validation failed',
        'message' => $e->getMessage()
    ], 500);
} 