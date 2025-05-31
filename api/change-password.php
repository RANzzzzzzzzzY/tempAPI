<?php
require_once '../config/database.php';
require_once '../includes/Utils.php';
require_once 'middleware/verify_api_key.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key');

// Debug: Log all headers
error_log("Received Headers: " . print_r(getallheaders(), true));

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Utils::sendJsonResponse(['error' => 'Method not allowed'], 405);
}

// Verify API key
$client = verifyApiKey();

// Get and validate input
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    Utils::sendJsonResponse(['error' => 'Invalid JSON data'], 400);
}

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

// If no Authorization header found, check for lowercase
if (!$authHeader && isset($headers['authorization'])) {
    $authHeader = $headers['authorization'];
}

if (!$authHeader) {
    Utils::sendJsonResponse(['error' => 'Authentication required - No header found'], 401);
}

// Remove 'Bearer ' if it exists, otherwise use the token as is
if (preg_match('/^Bearer\s+(.+)$/i', $authHeader, $matches)) {
    $token = trim($matches[1]);
} else {
    $token = trim($authHeader);
}

if (empty($token)) {
    Utils::sendJsonResponse(['error' => 'Authentication required - Empty token'], 401);
}

// Debug log
error_log("Processing token: " . $token);

$oldPassword = $data['old_password'] ?? '';
$newPassword = $data['new_password'] ?? '';
$confirmPassword = $data['confirm_password'] ?? '';

// Validate input
if (!$oldPassword || !$newPassword || !$confirmPassword) {
    Utils::sendJsonResponse(['error' => 'Old password, new password, and confirm password are required'], 400);
}

// Check if new password matches confirm password
if ($newPassword !== $confirmPassword) {
    Utils::sendJsonResponse(['error' => 'New password and confirm password do not match'], 400);
}

// Validate new password requirements
if (!Utils::isValidPassword($newPassword)) {
    Utils::sendJsonResponse(['error' => 'New password must be at least 8 characters and contain uppercase, lowercase, and numbers'], 400);
}

// Check if new password is different from old password
if ($oldPassword === $newPassword) {
    Utils::sendJsonResponse(['error' => 'New password must be different from old password'], 400);
}

try {
    // Verify token and get user info
    $stmt = $pdo->prepare("
        SELECT 
            at.user_id,
            at.expires_at,
            au.password_hash
        FROM auth_tokens at
        JOIN api_users au ON au.id = at.user_id
        WHERE at.token = ? AND au.dev_id = ?
        ORDER BY at.created_at DESC
        LIMIT 1
    ");
    
    $stmt->execute([$token, $client['dev_id']]);
    $tokenData = $stmt->fetch();

    if (!$tokenData || strtotime($tokenData['expires_at']) < time()) {
        Utils::sendJsonResponse(['error' => 'Invalid or expired token'], 401);
    }

    // Verify old password
    if (!password_verify($oldPassword, $tokenData['password_hash'])) {
        Utils::sendJsonResponse(['error' => 'Old password is incorrect'], 400);
    }

    // Begin transaction
    $pdo->beginTransaction();

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
        'message' => 'Password changed successfully'
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log($e->getMessage());
    Utils::sendJsonResponse(['error' => 'Failed to change password. Please try again.'], 500);
} 