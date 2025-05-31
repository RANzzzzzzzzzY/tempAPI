<?php
// Prevent any HTML error output
ini_set('display_errors', 0);
ini_set('html_errors', 0);
error_reporting(E_ALL);

// Enable error logging
ini_set('log_errors', 1);
ini_set('error_log', '../php_errors.log');

// Function to handle errors
function handleError($errno, $errstr, $errfile, $errline) {
    error_log("PHP Error [$errno]: $errstr in $errfile on line $errline");
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'Internal server error',
        'debug_message' => "[$errno] $errstr"
    ]);
    exit(1);
}

// Set error handler
set_error_handler('handleError');

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
    error_log("Starting registration process...");
    
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
    $requiredFields = ['fullName', 'email', 'systemName', 'password'];
    $missingFields = [];
    
    foreach ($requiredFields as $field) {
        if (empty($data[$field])) {
            $missingFields[] = $field;
        }
    }

    if (!empty($missingFields)) {
        throw new Exception("Missing required fields: " . implode(', ', $missingFields));
    }

    // Check database connection
    if (!isset($pdo)) {
        throw new Exception("Database connection not established");
    }

    // Begin transaction
    $pdo->beginTransaction();
    error_log("Transaction started");

    try {
        // Create account with explicit column names
        $sql = "INSERT INTO dev_accounts (email, password_hash, full_name, is_email_verified) VALUES (:email, :password_hash, :full_name, :is_email_verified)";
        error_log("Preparing SQL: " . $sql);
        
        $stmt = $pdo->prepare($sql);
        $params = [
            ':email' => $data['email'],
            ':password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
            ':full_name' => $data['fullName'],
            ':is_email_verified' => true
        ];
        error_log("Parameters: " . print_r($params, true));
        
        $stmt->execute($params);
        $devId = $pdo->lastInsertId();
        error_log("Created dev account with ID: " . $devId);

        // Generate API key with prefix for better readability
        $apiKey = 'ak_' . bin2hex(random_bytes(16));
        
        // Save API key with explicit column names
        $sql = "INSERT INTO api_clients (dev_id, system_name, api_key, is_active) VALUES (:dev_id, :system_name, :api_key, :is_active)";
        error_log("Preparing SQL: " . $sql);
        
        $stmt = $pdo->prepare($sql);
        $params = [
            ':dev_id' => $devId,
            ':system_name' => $data['systemName'],
            ':api_key' => $apiKey,
            ':is_active' => true
        ];
        error_log("Parameters: " . print_r($params, true));
        
        $stmt->execute($params);

        // Commit transaction
        $pdo->commit();
        error_log("Transaction committed successfully");

        // Send success response
        echo json_encode([
            'success' => true,
            'message' => 'Registration successful!',
            'api_key' => $apiKey
        ]);

    } catch (PDOException $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        error_log("Database error during registration: " . $e->getMessage());
        error_log("SQL State: " . $e->getCode());
        throw new Exception("Database error during registration: " . $e->getMessage());
    }

} catch (Exception $e) {
    error_log("Registration error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    // Send error response
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'debug_info' => [
            'php_version' => PHP_VERSION,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'
        ]
    ]);
} 