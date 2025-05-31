<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    require_once 'config/database.php';
    
    echo "Database connection successful!\n\n";
    
    // Check api_users table
    $stmt = $pdo->query("SHOW CREATE TABLE api_users");
    $result = $stmt->fetch();
    echo "api_users table structure:\n";
    print_r($result);
    echo "\n\n";
    
    // Check api_clients table
    $stmt = $pdo->query("SHOW CREATE TABLE api_clients");
    $result = $stmt->fetch();
    echo "api_clients table structure:\n";
    print_r($result);
    echo "\n\n";
    
    // Check email_otps table
    $stmt = $pdo->query("SHOW CREATE TABLE email_otps");
    $result = $stmt->fetch();
    echo "email_otps table structure:\n";
    print_r($result);
    echo "\n\n";
    
    // Check auth_tokens table
    $stmt = $pdo->query("SHOW CREATE TABLE auth_tokens");
    $result = $stmt->fetch();
    echo "auth_tokens table structure:\n";
    print_r($result);
    echo "\n\n";
    
    // Check for existing records
    echo "Checking existing records:\n";
    
    $tables = ['api_users', 'api_clients', 'email_otps', 'auth_tokens'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
        $count = $stmt->fetch()['count'];
        echo "$table: $count records\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString();
} 