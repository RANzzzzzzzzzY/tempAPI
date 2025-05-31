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

// Log request details
error_log("=== API User Registration Request ===");
error_log("Request URI: " . $_SERVER['REQUEST_URI']);
error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);
error_log("Content Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'Not set'));

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
    error_log("Starting API user registration process...");
    
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
        error_log("Database configuration file not found!");
        throw new Exception('Database configuration file not found');
    }
    
    require_once '../config/database.php';
    error_log("Database configuration loaded successfully");
    
    // Verify API key
    $stmt = $pdo->prepare("
        SELECT dev_id 
        FROM api_clients 
        WHERE api_key = ? AND is_active = TRUE
    ");
    $stmt->execute([$apiKey]);
    $client = $stmt->fetch();
    error_log("API Key verification result: " . ($client ? 'Valid' : 'Invalid'));

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
        error_log("JSON parsing error: " . json_last_error_msg());
        throw new Exception("Invalid JSON: " . json_last_error_msg());
    }

    // Validate required fields
    $requiredFields = ['email', 'password'];
    $missingFields = [];
    
    foreach ($requiredFields as $field) {
        if (empty($data[$field])) {
            $missingFields[] = $field;
        }
    }

    if (!empty($missingFields)) {
        error_log("Missing fields: " . implode(', ', $missingFields));
        throw new Exception("Missing required fields: " . implode(', ', $missingFields));
    }

    // Validate email format
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        error_log("Invalid email format: " . $data['email']);
        throw new Exception("Invalid email format");
    }

    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id, is_email_verified FROM api_users WHERE email = ? AND dev_id = ?");
    $stmt->execute([$data['email'], $client['dev_id']]);
    $existingUser = $stmt->fetch();
    error_log("Email existence check: " . ($existingUser ? ($existingUser['is_email_verified'] ? 'Exists and Verified' : 'Exists but Not Verified') : 'New'));
    
    if ($existingUser && $existingUser['is_email_verified']) {
        throw new Exception("Email already registered and verified");
    } else if ($existingUser) {
        // If email exists but not verified, delete the existing user record
        $stmt = $pdo->prepare("DELETE FROM api_users WHERE id = ?");
        $stmt->execute([$existingUser['id']]);
        error_log("Deleted unverified user account to allow re-registration");
    }

    // Begin transaction
    $pdo->beginTransaction();
    error_log("Database transaction started");

    try {
        // Create user account
        $stmt = $pdo->prepare("
            INSERT INTO api_users (dev_id, email, password_hash, is_email_verified, is_active)
            VALUES (:dev_id, :email, :password_hash, :is_email_verified, :is_active)
        ");
        
        $params = [
            ':dev_id' => $client['dev_id'],
            ':email' => $data['email'],
            ':password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
            ':is_email_verified' => false,
            ':is_active' => true
        ];
        
        error_log("Attempting to insert user with params: " . print_r($params, true));
        $stmt->execute($params);
        
        $userId = $pdo->lastInsertId();
        error_log("User created with ID: " . $userId);

        // Generate otp
        $otp = rand(100000, 999999);
        error_log("Verification token generated: " . $otp);
        
        // Store otp
        $stmt = $pdo->prepare("
            INSERT INTO email_otps (dev_id, email, otp, purpose, expires_at)
            VALUES (:dev_id, :email, :otp, :purpose, DATE_ADD(NOW(), INTERVAL 5 MINUTE))
        ");
        
        $stmt->execute([
            ':dev_id' => $client['dev_id'],
            ':email' => $data['email'],
            ':otp' => $otp,
            ':purpose' => 'verification'
        ]);
        error_log("OTP stored");

        // Commit transaction
        $pdo->commit();
        error_log("Database transaction committed");

        // Create auth token
        $authToken = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));
        
        $stmt = $pdo->prepare("
            INSERT INTO auth_tokens (user_id, token, expires_at)
            VALUES (:user_id, :token, :expires_at)
        ");
        
        $stmt->execute([
            ':user_id' => $userId,
            ':token' => $authToken,
            ':expires_at' => $expiresAt
        ]);

        // Prepare response
        $response = [
            'success' => true,
            'message' => 'User registered successfully',
            'data' => [
                'user_id' => $userId,
                'email' => $data['email'],
                'auth_token' => $authToken,
                'expires_at' => $expiresAt,
                'is_verified' => false,
                'otp' => $otp
            ]
        ];
        
        error_log("Sending success response: " . print_r($response, true));
        echo json_encode($response);

    } catch (PDOException $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        error_log("Database error during registration: " . $e->getMessage());
        error_log("SQL State: " . $e->getCode());
        throw new Exception("Error creating user account: " . $e->getMessage());
    }

} catch (Exception $e) {
    error_log("API user registration error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    // Send error response
    http_response_code(400);
    $response = [
        'success' => false,
        'error' => $e->getMessage()
    ];
    error_log("Sending error response: " . print_r($response, true));
    echo json_encode($response);
} 