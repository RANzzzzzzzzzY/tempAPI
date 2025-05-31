<?php
// Prevent any HTML error output
ini_set('display_errors', 0);
ini_set('html_errors', 0);
error_reporting(E_ALL);

// Enable error logging
ini_set('log_errors', 1);
ini_set('error_log', '../php_errors.log');

// Log request details
error_log("=== API User Logout Request ===");
error_log("Request URI: " . $_SERVER['REQUEST_URI']);
error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept, X-API-Key, Authorization');
header('Access-Control-Max-Age: 86400');

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

require_once '../config/database.php';

try {
    // Get headers
    $headers = getallheaders();
    error_log("Request Headers: " . print_r($headers, true));
    
    $apiKey = $headers['X-API-Key'] ?? $headers['x-api-key'] ?? $headers['X-Api-Key'] ?? null;
    $authToken = $headers['Authorization'] ?? $headers['authorization'] ?? '';

    // Check API key
    if (!$apiKey) {
        throw new Exception('API key is required');
    }

    // Check auth token
    if (!$authToken) {
        throw new Exception('Authorization token is required');
    }

    // Remove 'Bearer ' prefix if present
    $authToken = str_replace('Bearer ', '', $authToken);

    // Begin transaction
    $pdo->beginTransaction();
    error_log("Starting logout transaction");

    try {
        // Validate API key
        $stmt = $pdo->prepare("
            SELECT dev_id 
            FROM api_clients 
            WHERE api_key = ? AND is_active = TRUE
            LIMIT 1
        ");
        $stmt->execute([$apiKey]);
        $client = $stmt->fetch();

        if (!$client) {
            throw new Exception('Invalid or inactive API key');
        }

        // Find and validate the auth token
        $stmt = $pdo->prepare("
            SELECT at.id, at.user_id, au.dev_id
            FROM auth_tokens at
            JOIN api_users au ON at.user_id = au.id
            WHERE at.token = ?
            AND at.expires_at > NOW()
            AND au.dev_id = ?
            AND au.is_active = TRUE
            LIMIT 1
        ");
        $stmt->execute([$authToken, $client['dev_id']]);
        $token = $stmt->fetch();

        if (!$token) {
            throw new Exception('Invalid or expired token');
        }

        // Delete the token
        $stmt = $pdo->prepare("DELETE FROM auth_tokens WHERE id = ?");
        $stmt->execute([$token['id']]);

        // Commit transaction
        $pdo->commit();
        error_log("Logout transaction committed successfully");

        // Send success response
        echo json_encode([
            'success' => true,
            'message' => 'Successfully logged out'
        ]);

    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Error during logout transaction: " . $e->getMessage());
        throw $e;
    }

} catch (Exception $e) {
    error_log("API user logout error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 