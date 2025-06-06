<?php
require_once '../config/database.php';
require_once '../config/mail.php';
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

// Verify API key and get client info
$client = verifyApiKey();

// Get API key from headers
$headers = getallheaders();
$apiKey = null;

foreach ($headers as $key => $value) {
    if (strtolower($key) === 'x-api-key') {
        $apiKey = $value;
        break;
    }
}

// Get and validate input
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    Utils::sendJsonResponse([
        'success' => false,
        'error' => 'Invalid request',
        'message' => 'Request body must be valid JSON'
    ], 400);
}

// Get token from Authorization header
$token = null;

foreach ($headers as $key => $value) {
    if (strtolower($key) === 'authorization') {
        $token = $value;
        break;
    }
}

if (!$token) {
    Utils::sendJsonResponse([
        'success' => false,
        'error' => 'Authentication required',
        'message' => 'Authorization header is required'
    ], 401);
}

// Remove "Bearer " prefix if present (case-insensitive)
if (stripos($token, 'Bearer ') === 0) {
    $token = trim(substr($token, 7));
}

$otp = Utils::sanitizeInput($data['otp'] ?? '');
$newPassword = $data['new_password'] ?? '';

// Validate required fields
$missingFields = [];
if (!$otp) $missingFields[] = 'otp';
if (!$newPassword) $missingFields[] = 'new_password';

if (!empty($missingFields)) {
    Utils::sendJsonResponse([
        'success' => false,
        'error' => 'Missing required fields',
        'message' => 'The following fields are required: ' . implode(', ', $missingFields),
        'required_fields' => ['otp', 'new_password']
    ], 400);
}

// Validate password requirements
if (!Utils::isValidPassword($newPassword)) {
    Utils::sendJsonResponse([
        'success' => false,
        'error' => 'Invalid password format',
        'message' => 'Password must be at least 8 characters and contain uppercase, lowercase, and numbers',
        'requirements' => [
            'min_length' => 8,
            'must_contain' => ['uppercase', 'lowercase', 'numbers']
        ]
    ], 400);
}

try {
    // Begin transaction
    $pdo->beginTransaction();

    error_log("Debug - Attempting password reset with token length: " . strlen($token));

    // Verify token and get user info
    $stmt = $pdo->prepare("
        SELECT 
            at.user_id,
            at.expires_at,
            au.email,
            au.dev_id
        FROM auth_tokens at
        JOIN api_users au ON au.id = at.user_id
        JOIN api_clients ac ON au.dev_id = ac.dev_id
        WHERE at.token = ? 
        AND au.dev_id = ?
        AND ac.api_key = ?
        ORDER BY at.created_at DESC
        LIMIT 1
    ");
    
    $stmt->execute([$token, $client['dev_id'], $apiKey]);
    $tokenData = $stmt->fetch();
    
    error_log("Debug - Token query completed. Found data: " . ($tokenData ? 'yes' : 'no'));
    $devID = $client['dev_id'];
    if (!$tokenData) {
        $pdo->rollBack();
        Utils::sendJsonResponse([
            'success' => false,
            'error' => 'Invalid token',
            'message' => "$token; $devID; $apiKey",
            'debug' => [
                'token_length' => strlen($token),
                'dev_id' => $client['dev_id']
            ]
        ], 401);
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

    // Verify OTP
    error_log("Debug - Starting OTP verification process");
    error_log("Debug - User ID: " . $tokenData['user_id'] . ", OTP provided: " . $otp);

    // First check if OTP exists
    $stmt = $pdo->prepare("
        SELECT id, otp, expires_at 
        FROM password_reset_otps 
        WHERE api_user_id = ?
        ORDER BY created_at DESC
        LIMIT 1
    ");
    
    $stmt->execute([$tokenData['user_id']]);
    $otpData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    error_log("Debug - Found OTP record: " . ($otpData ? "Yes" : "No"));
    if ($otpData) {
        error_log("Debug - Stored OTP: " . $otpData['otp']);
        error_log("Debug - Expires at: " . $otpData['expires_at']);
        error_log("Debug - Current time: " . date('Y-m-d H:i:s'));
        error_log("Debug - OTP match: " . ($otpData['otp'] === $otp ? "Yes" : "No"));
    }

    if (!$otpData || $otpData['otp'] !== $otp || strtotime($otpData['expires_at']) < time()) {
        $pdo->rollBack();
        Utils::sendJsonResponse([
            'success' => false,
            'error' => 'Invalid OTP',
            'message' => 'The provided OTP is invalid or has expired',
            'debug' => [
                'user_id' => $tokenData['user_id'],
                'otp_length' => strlen($otp),
                'otp_exists' => $otpData ? true : false,
                'otp_expired' => $otpData ? (strtotime($otpData['expires_at']) < time() ? true : false) : null,
                'otp_matches' => $otpData ? ($otpData['otp'] === $otp) : null
            ]
        ], 400);
    }

    // Delete used OTP
    $stmt = $pdo->prepare("DELETE FROM password_reset_otps WHERE id = ?");
    $stmt->execute([$otpData['id']]);

    // Update password
    $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE api_users SET password_hash = ? WHERE id = ?");
    $stmt->execute([$newPasswordHash, $tokenData['user_id']]);

    // Invalidate all auth tokens except current one
    $stmt = $pdo->prepare("DELETE FROM auth_tokens WHERE user_id = ? AND token != ?");
    $stmt->execute([$tokenData['user_id'], $token]);

    // Commit transaction
    $pdo->commit();

    Utils::sendJsonResponse([
        'success' => true,
        'message' => 'Password reset successfully',
        'data' => [
            'email' => $tokenData['email'],
            'token_expires_at' => $tokenData['expires_at']
        ]
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Error in reset-password.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    Utils::sendJsonResponse([
        'success' => false,
        'error' => 'Server error',
        'message' => 'An unexpected error occurred while resetting your password. Please try again later.',
        'debug' => [
            'error_message' => $e->getMessage(),
            'token_length' => strlen($token),
            'dev_id' => $client['dev_id']
        ]
    ], 500);
} 