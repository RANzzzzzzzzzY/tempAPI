<?php
// Set up error logging to a file in the project directory
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/debug.log');

// Start with detailed logging
error_log("=== VERIFY EMAIL ENDPOINT HIT ===");
error_log("Time: " . date('Y-m-d H:i:s'));

// Log all headers received
$allHeaders = getallheaders();
error_log("All headers received: " . print_r($allHeaders, true));

// Log all SERVER variables
error_log("All SERVER variables: " . print_r($_SERVER, true));

require_once '../config/database.php';
require_once '../includes/Utils.php';
require_once 'middleware/verify_api_key.php';

// Debug: Log everything we receive
$debug_info = [
    'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'],
    'CONTENT_TYPE' => $_SERVER['CONTENT_TYPE'] ?? 'not set',
    'RAW_HEADERS' => getallheaders(),
    'SERVER_VARS' => array_filter($_SERVER, function($key) {
        return strpos($key, 'HTTP_') === 0 || strpos($key, 'CONTENT_') === 0;
    }, ARRAY_FILTER_USE_KEY),
    'REQUEST_BODY' => file_get_contents('php://input'),
    'POST_DATA' => $_POST,
    'NGROK_INFO' => [
        'is_ngrok' => isset($_SERVER['HTTP_X_FORWARDED_FOR']),
        'forwarded_for' => $_SERVER['HTTP_X_FORWARDED_FOR'] ?? 'not set',
        'forwarded_proto' => $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? 'not set'
    ]
];

error_log("FULL DEBUG INFO: " . print_r($debug_info, true));

// Enhanced CORS headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-API-Key, Authorization, x-api-key');
header('Access-Control-Expose-Headers: X-API-Key');

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

// Get and validate input
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    Utils::sendJsonResponse([
        'success' => false,
        'error' => 'Invalid request',
        'message' => 'Request body must be valid JSON'
    ], 400);
}

// Get API key with detailed logging
$apiKey = null;

// Log the specific header we're looking for
error_log("Checking for HTTP_X_API_KEY: " . (isset($_SERVER['HTTP_X_API_KEY']) ? $_SERVER['HTTP_X_API_KEY'] : 'not set'));

// Direct check for the header we know works
if (isset($_SERVER['HTTP_X_API_KEY'])) {
    $apiKey = $_SERVER['HTTP_X_API_KEY'];
    error_log("Found API key in HTTP_X_API_KEY: " . $apiKey);
}

// Log all possible variations we're checking
$headers = getallheaders();
error_log("Checking X-Api-Key: " . (isset($headers['X-Api-Key']) ? $headers['X-Api-Key'] : 'not set'));
error_log("Checking x-api-key: " . (isset($headers['x-api-key']) ? $headers['x-api-key'] : 'not set'));

// Fallback checks if needed
if (!$apiKey) {
    if (isset($headers['X-Api-Key'])) {
        $apiKey = $headers['X-Api-Key'];
        error_log("Found API key in X-Api-Key header: " . $apiKey);
    } elseif (isset($headers['x-api-key'])) {
        $apiKey = $headers['x-api-key'];
        error_log("Found API key in x-api-key header: " . $apiKey);
    }
}

error_log("Final API key value: " . ($apiKey ? $apiKey : 'not found'));

if (!$apiKey) {
    Utils::sendJsonResponse([
        'success' => false,
        'error' => 'Authentication required',
        'message' => 'API key is required in X-API-Key header',
        'debug_info' => [
            'received_headers' => $headers,
            'server_vars' => array_filter($_SERVER, function($key) {
                return strpos($key, 'HTTP_') === 0 || strpos($key, 'CONTENT_') === 0;
            }, ARRAY_FILTER_USE_KEY),
            'request_method' => $_SERVER['REQUEST_METHOD']
        ]
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

$email = Utils::sanitizeInput($data['email'] ?? '');
$otp = Utils::sanitizeInput($data['otp'] ?? '');

// Validate required fields
$missingFields = [];
if (!$email) $missingFields[] = 'email';
if (!$otp) $missingFields[] = 'otp';

if (!empty($missingFields)) {
    Utils::sendJsonResponse([
        'success' => false,
        'error' => 'Missing required fields',
        'message' => 'The following fields are required: ' . implode(', ', $missingFields),
        'required_fields' => ['email', 'otp']
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
    // Get user account with API key check
    $stmt = $pdo->prepare("
        SELECT au.id, au.is_email_verified, au.dev_id
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

    if ($user['is_email_verified']) {
        Utils::sendJsonResponse([
            'success' => true,
            'message' => 'Email is already verified'
        ]);
    }

    // Get latest OTP with API key verification
    $stmt = $pdo->prepare("
        SELECT eo.id, eo.otp, eo.expires_at 
        FROM email_otps eo
        JOIN api_clients ac ON eo.dev_id = ac.dev_id
        WHERE eo.email = ? 
        AND eo.dev_id = ?
        AND ac.api_key = ?
        ORDER BY eo.created_at DESC 
        LIMIT 1
    ");
    $stmt->execute([$email, $client['dev_id'], $apiKey]);
    $otpRecord = $stmt->fetch();

    if (!$otpRecord) {
        Utils::sendJsonResponse([
            'success' => false,
            'error' => 'OTP not found',
            'message' => 'No verification code found for this email'
        ], 404);
    }

    // Check if OTP is expired
    if (strtotime($otpRecord['expires_at']) < time()) {
        Utils::sendJsonResponse([
            'success' => false,
            'error' => 'OTP expired',
            'message' => 'The verification code has expired. Please request a new one',
            'expired_at' => $otpRecord['expires_at']
        ], 400);
    }

    // Verify OTP
    if ($otpRecord['otp'] !== $otp) {
        Utils::sendJsonResponse([
            'success' => false,
            'error' => 'Invalid OTP',
            'message' => 'The verification code is incorrect'
        ], 400);
    }

    // Begin transaction
    $pdo->beginTransaction();

    try {
        // Mark email as verified
        $stmt = $pdo->prepare("UPDATE api_users SET is_email_verified = TRUE WHERE id = ?");
        $stmt->execute([$user['id']]);

        // Delete used OTP
        $stmt = $pdo->prepare("DELETE FROM email_otps WHERE id = ?");
        $stmt->execute([$otpRecord['id']]);

        // Create auth token
        $authToken = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));

        // Delete any existing tokens
        $stmt = $pdo->prepare("DELETE FROM auth_tokens WHERE user_id = ?");
        $stmt->execute([$user['id']]);

        // Insert new token
        $stmt = $pdo->prepare("
            INSERT INTO auth_tokens (user_id, token, expires_at)
            VALUES (:user_id, :token, :expires_at)
        ");

        $stmt->execute([
            ':user_id' => $user['id'],
            ':token' => $authToken,
            ':expires_at' => $expiresAt
        ]);

        // Commit transaction
        $pdo->commit();

        Utils::sendJsonResponse([
            'success' => true,
            'message' => 'Email verified successfully',
            'data' => [
                'user_id' => $user['id'],
                'email' => $email,
                'auth_token' => $authToken,
                'expires_at' => $expiresAt
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