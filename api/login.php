<?php
// Prevent any HTML error output
ini_set('display_errors', 0);
ini_set('html_errors', 0);
error_reporting(E_ALL);

// Enable error logging
ini_set('log_errors', 1);
ini_set('error_log', '../php_errors.log');

// Debug: Log all headers
error_log("=== DEBUG: ALL REQUEST HEADERS ===");
$allHeaders = getallheaders();
foreach ($allHeaders as $name => $value) {
    error_log("Header '$name': $value");
}

// Debug: Log specific header variations
error_log("=== DEBUG: API KEY HEADER CHECKS ===");
error_log("X-API-Key (exact): " . (isset($allHeaders['X-API-Key']) ? $allHeaders['X-API-Key'] : 'not set'));
error_log("x-api-key (lowercase): " . (isset($allHeaders['x-api-key']) ? $allHeaders['x-api-key'] : 'not set'));
error_log("X-Api-Key (mixed): " . (isset($allHeaders['X-Api-Key']) ? $allHeaders['X-Api-Key'] : 'not set'));

// Debug: Log SERVER variables
error_log("=== DEBUG: SERVER VARIABLES ===");
foreach ($_SERVER as $key => $value) {
    if (strpos($key, 'HTTP_') === 0) {
        error_log("$key: $value");
    }
}

// Allow CORS from any origin during testing
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept, X-API-Key, x-api-key, X-Api-Key, Authorization');
header('Access-Control-Expose-Headers: X-API-Key');
header('Access-Control-Max-Age: 86400'); // 24 hours cache for preflight
header('Content-Type: application/json');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Method not allowed'
    ]);
    exit();
}

try {
    error_log("Starting API user login process...");
    
    // Get API key from multiple sources
    $headers = getallheaders();
    error_log("Raw headers: " . print_r($headers, true));
    
    // Try to get API key from different sources
    $apiKey = null;
    
    // 1. Try getallheaders() with different cases
    $headerNames = ['X-API-Key', 'x-api-key', 'X-Api-Key'];
    foreach ($headerNames as $name) {
        if (isset($headers[$name])) {
            $apiKey = $headers[$name];
            error_log("Found API key in getallheaders() with name: $name");
            break;
        }
    }
    
    // 2. Try $_SERVER variables if still not found
    if (!$apiKey) {
        $serverKeys = [
            'HTTP_X_API_KEY',
            'HTTP_X_API_KEY',
            'REDIRECT_HTTP_X_API_KEY'
        ];
        
        foreach ($serverKeys as $key) {
            if (isset($_SERVER[$key])) {
                $apiKey = $_SERVER[$key];
                error_log("Found API key in \$_SERVER[$key]");
                break;
            }
        }
    }
    
    // 3. Try Apache headers if still not found
    if (!$apiKey && function_exists('apache_request_headers')) {
        $apacheHeaders = apache_request_headers();
        foreach ($headerNames as $name) {
            if (isset($apacheHeaders[$name])) {
                $apiKey = $apacheHeaders[$name];
                error_log("Found API key in apache_request_headers() with name: $name");
                break;
            }
        }
    }
    
    error_log("Final API Key status: " . ($apiKey ? "Found: $apiKey" : "Not found"));
    
    if (!$apiKey) {
        throw new Exception("API key is required");
    }

    // Check if config files exist
    if (!file_exists('../config/database.php')) {
        throw new Exception('Database configuration file not found');
    }
    
    require_once '../config/database.php';
    
    // Verify API key
    $stmt = $pdo->prepare("
        SELECT dev_id 
        FROM api_clients 
        WHERE api_key = ? AND is_active = TRUE
    ");
    $stmt->execute([$apiKey]);
    $client = $stmt->fetch();

    if (!$client) {
        throw new Exception("Invalid or inactive API key");
    }

    // Get and log raw input
    $rawInput = file_get_contents('php://input');
    error_log("Raw input received: " . $rawInput);

    // Parse JSON
    $data = json_decode($rawInput, true);
    error_log("Parsed data: " . print_r($data, true));

    // Check if JSON parsing failed
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Invalid JSON: " . json_last_error_msg());
    }

    // Validate required fields
    if (empty($data['email']) || empty($data['password'])) {
        throw new Exception("Email and password are required");
    }

    // Get user by email and dev_id
    $stmt = $pdo->prepare("
        SELECT id, email, password_hash, is_email_verified, is_active 
        FROM api_users 
        WHERE email = ? AND dev_id = ?
    ");
    
    $stmt->execute([$data['email'], $client['dev_id']]);
    $user = $stmt->fetch();

    if (!$user) {
        throw new Exception("Invalid email or password");
    }

    // Check if account is active
    if (!$user['is_active']) {
        throw new Exception("Account is deactivated. Please contact support.");
    }

    // Verify password
    if (!password_verify($data['password'], $user['password_hash'])) {
        throw new Exception("Invalid email or password");
    }

    // Check if email is verified
    if (!$user['is_email_verified']) {
        throw new Exception("Please verify your email before logging in");
    }

    // Begin transaction for token management
    $pdo->beginTransaction();

    try {
        // Invalidate any existing tokens for this user
        $stmt = $pdo->prepare("
            DELETE FROM auth_tokens 
            WHERE user_id = ? AND expires_at > NOW()
        ");
        $stmt->execute([$user['id']]);

        // Generate new auth token
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+30 minutes'));
        
        // Store auth token
        $stmt = $pdo->prepare("
            INSERT INTO auth_tokens (user_id, token, expires_at)
            VALUES (:user_id, :token, NOW() + INTERVAL 30 MINUTE)
        ");
        
        $stmt->execute([
            ':user_id' => $user['id'],
            ':token' => $token,
        ]);

        // Commit transaction
        $pdo->commit();

        // Send success response
        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user_id' => $user['id'],
                'email' => $user['email'],
                'auth_token' => $token,
                'expires_at' => $expiresAt
            ]
        ]);

    } catch (Exception $e) {
        $pdo->rollBack();
        throw new Exception("Error during login: " . $e->getMessage());
    }

} catch (Exception $e) {
    error_log("API user login error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    // Send error response
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 