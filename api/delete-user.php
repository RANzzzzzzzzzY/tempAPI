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

// Get and validate input
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    Utils::sendJsonResponse([
        'success' => false,
        'error' => 'Invalid request',
        'message' => 'Request body must be valid JSON'
    ], 400);
}

$email = Utils::sanitizeInput($data['email'] ?? '');

// Validate required fields
if (!$email) {
    Utils::sendJsonResponse([
        'success' => false,
        'error' => 'Missing required fields',
        'message' => 'Email is required',
        'required_fields' => ['email']
    ], 400);
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    Utils::sendJsonResponse([
        'success' => false,
        'error' => 'Invalid email format',
        'message' => 'Please provide a valid email address',
        'provided_email' => $email
    ], 400);
}

try {
    // Begin transaction
    $pdo->beginTransaction();

    // Verify token and get user info
    $stmt = $pdo->prepare("
        SELECT 
            at.user_id,
            at.expires_at,
            au.dev_id,
            au.email
        FROM auth_tokens at
        JOIN api_users au ON au.id = at.user_id
        WHERE at.token = ? 
        AND au.dev_id = ?
        AND au.email = ?
        ORDER BY at.created_at DESC
        LIMIT 1
    ");
    
    $stmt->execute([$token, $client['dev_id'], $email]);
    $tokenData = $stmt->fetch();

    if (!$tokenData) {
        $pdo->rollBack();
        Utils::sendJsonResponse([
            'success' => false,
            'error' => 'Invalid request',
            'message' => 'No matching user found with the provided email and token'
        ], 404);
    }

    if (strtotime($tokenData['expires_at']) < time()) {
        $pdo->rollBack();
        Utils::sendJsonResponse([
            'success' => false,
            'error' => 'Token expired',
            'message' => 'The authentication token has expired',
            'expired_at' => $tokenData['expires_at']
        ], 401);
    }

    // Delete all auth tokens for the user
    $stmt = $pdo->prepare("DELETE FROM auth_tokens WHERE user_id = ?");
    $stmt->execute([$tokenData['user_id']]);

    // Delete all OTPs for the user
    $stmt = $pdo->prepare("
        DELETE FROM email_otps 
        WHERE dev_id = ? AND email = ?
    ");
    $stmt->execute([$tokenData['dev_id'], $email]);

    // Delete password reset OTPs
    $stmt = $pdo->prepare("
        DELETE FROM password_reset_otps 
        WHERE api_user_id = ?
    ");
    $stmt->execute([$tokenData['user_id']]);

    // Delete the user
    $stmt = $pdo->prepare("DELETE FROM api_users WHERE id = ? AND dev_id = ?");
    $stmt->execute([$tokenData['user_id'], $tokenData['dev_id']]);

    // Commit transaction
    $pdo->commit();

    Utils::sendJsonResponse([
        'success' => true,
        'message' => 'User account deleted successfully',
        'data' => [
            'email' => $email
        ]
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log($e->getMessage());
    Utils::sendJsonResponse([
        'success' => false,
        'error' => 'Server error',
        'message' => 'Failed to delete user account. Please try again.'
    ], 500);
} 