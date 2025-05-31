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

// Validate API key presence
$headers = getallheaders();
$apiKey = null;

// Check for different possible header cases
foreach ($headers as $key => $value) {
    if (strtolower($key) === 'x-api-key') {
        $apiKey = $value;
        break;
    }
}

if (!$apiKey) {
    Utils::sendJsonResponse([
        'success' => false,
        'error' => 'Authentication required',
        'message' => 'API key is required in X-API-Key header'
    ], 401);
}

// Verify API key and get client info
try {
    $client = verifyApiKey();
} catch (Exception $e) {
    Utils::sendJsonResponse([
        'success' => false,
        'error' => 'Invalid API key',
        'message' => 'The provided API key is invalid or inactive'
    ], 401);
}

// Get and validate input
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    Utils::sendJsonResponse(['error' => 'Invalid JSON data'], 400);
}

$email = Utils::sanitizeInput($data['email'] ?? '');
$purpose = Utils::sanitizeInput($data['purpose'] ?? '');

// Validate required fields
$missingFields = [];
if (!$email) $missingFields[] = 'email';
if (!$purpose) $missingFields[] = 'purpose';

if (!empty($missingFields)) {
    Utils::sendJsonResponse([
        'error' => 'Missing required fields: ' . implode(', ', $missingFields),
        'required_fields' => ['email', 'purpose']
    ], 400);
}

// Validate purpose
$validPurposes = ['email-verification', 'password-reset'];
if (!in_array($purpose, $validPurposes)) {
    Utils::sendJsonResponse([
        'error' => 'Invalid purpose provided',
        'valid_purposes' => $validPurposes,
        'message' => 'Purpose must be either "email-verification" or "password-reset"'
    ], 400);
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    Utils::sendJsonResponse([
        'error' => 'Invalid email format',
        'provided_email' => $email
    ], 400);
}

try {
    // Get user account with API key check
    $stmt = $pdo->prepare("
        SELECT au.id, au.is_email_verified 
        FROM api_users au
        JOIN api_clients ac ON au.dev_id = ac.dev_id
        WHERE au.email = ? 
        AND au.dev_id = ?
        AND ac.api_key = ?
    ");
    $stmt->execute([$email, $client['dev_id'], $apiKey]);
    $user = $stmt->fetch();

    if (!$user) {
        Utils::sendJsonResponse([
            'success' => false,
            'error' => 'User not found',
            'message' => 'No user found with this email for the provided API key'
        ], 404);
    }

    // For email verification, check if already verified
    if ($purpose === 'email-verification' && $user['is_email_verified']) {
        Utils::sendJsonResponse([
            'success' => false,
            'error' => 'Already verified',
            'message' => 'This email is already verified'
        ], 400);
    }

    // Check for rate limiting with API key
    if ($purpose === 'email-verification') {
        $stmt = $pdo->prepare("
            SELECT eo.created_at, eo.expires_at 
            FROM email_otps eo
            JOIN api_clients ac ON eo.dev_id = ac.dev_id
            WHERE eo.email = ? 
            AND eo.dev_id = ?
            AND ac.api_key = ?
            AND eo.expires_at > NOW()
            ORDER BY eo.created_at DESC 
            LIMIT 1
        ");
    } else {
        $stmt = $pdo->prepare("
            SELECT created_at, expires_at 
            FROM password_reset_otps 
            WHERE api_user_id = ? 
            AND expires_at > NOW()
            ORDER BY created_at DESC 
            LIMIT 1
        ");
    }

    $stmt->execute($purpose === 'email-verification' ? 
        [$email, $client['dev_id'], $apiKey] : 
        [$user['id']]
    );
    $existingOtp = $stmt->fetch();

    if ($existingOtp) {
        $lastRequestTime = strtotime($existingOtp['created_at']);
        $timeElapsed = time() - $lastRequestTime;
        
        if ($timeElapsed < 60) {
            $waitTime = 60 - $timeElapsed;
            Utils::sendJsonResponse([
                'success' => false,
                'error' => 'Rate limit exceeded',
                'message' => "Please wait {$waitTime} seconds before requesting a new OTP",
                'wait_time' => $waitTime,
                'retry_after' => date('Y-m-d H:i:s', time() + $waitTime)
            ], 429);
        }
    }

    // Begin transaction
    $pdo->beginTransaction();

    try {
        // Generate new OTP (6 digits)
        $otp = sprintf('%06d', random_int(0, 999999));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+15 minutes'));

        if ($purpose === 'email-verification') {
            // Delete any existing email verification OTPs
            $stmt = $pdo->prepare("
                DELETE FROM email_otps 
                WHERE email = ? AND dev_id = ?
            ");
            $stmt->execute([$email, $client['dev_id']]);

            // Store new email verification OTP
            $stmt = $pdo->prepare("
                INSERT INTO email_otps (dev_id, email, otp, expires_at)
                VALUES (:dev_id, :email, :otp, :expires_at)
            ");
            
            $stmt->execute([
                ':dev_id' => $client['dev_id'],
                ':email' => $email,
                ':otp' => $otp,
                ':expires_at' => $expiresAt
            ]);
        } else {
            // For password reset
            // Delete any existing password reset OTPs
            $stmt = $pdo->prepare("
                DELETE FROM password_reset_otps 
                WHERE api_user_id = ?
            ");
            $stmt->execute([$user['id']]);

            // Store new password reset OTP
            $stmt = $pdo->prepare("
                INSERT INTO password_reset_otps (api_user_id, otp, expires_at)
                VALUES (:api_user_id, :otp, :expires_at)
            ");
            
            $stmt->execute([
                ':api_user_id' => $user['id'],
                ':otp' => $otp,
                ':expires_at' => $expiresAt
            ]);
        }

        // Generate new auth token
        $authToken = bin2hex(random_bytes(32));
        $tokenExpiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));

        // Delete any existing tokens for this user
        $stmt = $pdo->prepare("
            DELETE FROM auth_tokens 
            WHERE user_id = ?
        ");
        $stmt->execute([$user['id']]);

        // Store new auth token
        $stmt = $pdo->prepare("
            INSERT INTO auth_tokens (user_id, token, expires_at)
            VALUES (:user_id, :token, :expires_at)
        ");
        
        $stmt->execute([
            ':user_id' => $user['id'],
            ':token' => $authToken,
            ':expires_at' => $tokenExpiresAt
        ]);

        // Commit transaction
        $pdo->commit();

        Utils::sendJsonResponse([
            'success' => true,
            'message' => 'OTP generated successfully',
            'data' => [
                'user_id' => $user['id'],
                'email' => $email,
                'purpose' => $purpose,
                'otp' => $otp,
                'otp_expires_at' => $expiresAt,
                'auth_token' => $authToken,
                'token_expires_at' => $tokenExpiresAt
            ]
        ]);

    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log($e->getMessage());
    Utils::sendJsonResponse([
        'success' => false,
        'error' => 'Server error',
        'message' => 'An unexpected error occurred. Please try again later.'
    ], 500);
} 