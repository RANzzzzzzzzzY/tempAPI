<?php
// Prevent any HTML error output
ini_set('display_errors', 0);
ini_set('html_errors', 0);
error_reporting(E_ALL);

// Enable error logging
ini_set('log_errors', 1);
ini_set('error_log', '../php_errors.log');

// Allow CORS from any origin during testing
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept');
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
    error_log("Starting login process...");
    
    // Check if config files exist
    if (!file_exists('../config/database.php')) {
        throw new Exception('Database configuration file not found');
    }
    
    require_once '../config/database.php';
    
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

    // Check database connection
    if (!isset($pdo)) {
        throw new Exception("Database connection not established");
    }

    // Get user by email
    $stmt = $pdo->prepare("
        SELECT d.*, a.api_key, a.system_name 
        FROM dev_accounts d 
        LEFT JOIN api_clients a ON d.id = a.dev_id 
        WHERE d.email = :email
    ");
    
    $stmt->execute([':email' => $data['email']]);
    $user = $stmt->fetch();

    if (!$user) {
        throw new Exception("Invalid email or password");
    }

    // Verify password
    if (!password_verify($data['password'], $user['password_hash'])) {
        throw new Exception("Invalid email or password");
    }

    // Prepare response data
    $response = [
        'success' => true,
        'message' => 'Login successful',
        'data' => [
            'id' => $user['id'],
            'email' => $user['email'],
            'full_name' => $user['full_name'],
            'is_email_verified' => $user['is_email_verified'],
            'api_key' => $user['api_key'],
            'system_name' => $user['system_name']
        ]
    ];

    // Log successful login
    error_log("Successful login for user: " . $user['email']);

    // Send success response
    echo json_encode($response);

} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    // Send error response
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 