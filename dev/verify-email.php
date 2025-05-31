<?php
require_once '../config/database.php';
require_once '../includes/Utils.php';

header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Utils::sendJsonResponse(['error' => 'Method not allowed'], 405);
}

// Get and validate input
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    Utils::sendJsonResponse(['error' => 'Invalid JSON data'], 400);
}

$email = Utils::sanitizeInput($data['email'] ?? '');
$otp = Utils::sanitizeInput($data['otp'] ?? '');

// Validate input
if (!$email || !$otp) {
    Utils::sendJsonResponse(['error' => 'Email and OTP are required'], 400);
}

try {
    // Get developer account
    $stmt = $pdo->prepare("SELECT id, is_email_verified FROM dev_accounts WHERE email = ?");
    $stmt->execute([$email]);
    $dev = $stmt->fetch();

    if (!$dev) {
        Utils::sendJsonResponse(['error' => 'Invalid email'], 404);
    }

    if ($dev['is_email_verified']) {
        Utils::sendJsonResponse(['error' => 'Email already verified'], 400);
    }

    // Get latest OTP for verification
    $stmt = $pdo->prepare("
        SELECT otp_hash, expires_at 
        FROM email_otps 
        WHERE dev_id = ? AND purpose = 'verification'
        ORDER BY created_at DESC 
        LIMIT 1
    ");
    $stmt->execute([$dev['id']]);
    $otpRecord = $stmt->fetch();

    if (!$otpRecord) {
        Utils::sendJsonResponse(['error' => 'No verification code found'], 404);
    }

    // Check if OTP is expired
    if (strtotime($otpRecord['expires_at']) < time()) {
        Utils::sendJsonResponse(['error' => 'Verification code expired'], 400);
    }

    // Verify OTP
    if (!password_verify($otp, $otpRecord['otp_hash'])) {
        Utils::sendJsonResponse(['error' => 'Invalid verification code'], 400);
    }

    // Begin transaction
    $pdo->beginTransaction();

    // Mark email as verified
    $stmt = $pdo->prepare("UPDATE dev_accounts SET is_email_verified = TRUE WHERE id = ?");
    $stmt->execute([$dev['id']]);

    // Delete used OTP
    $stmt = $pdo->prepare("DELETE FROM email_otps WHERE dev_id = ? AND purpose = 'verification'");
    $stmt->execute([$dev['id']]);

    // Commit transaction
    $pdo->commit();

    Utils::sendJsonResponse(['message' => 'Email verified successfully']);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log($e->getMessage());
    Utils::sendJsonResponse(['error' => 'Verification failed. Please try again.'], 500);
} 