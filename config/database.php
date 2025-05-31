<?php
try {
    // Database configuration
    $host = 'localhost';
    $dbname = 'auth_api_system';
    $username = 'root';
    $password = '';
    
    // Create PDO connection with proper charset and error mode
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ]
    );

    // Test the connection and check if required tables exist
    $requiredTables = ['dev_accounts', 'api_clients', 'api_users', 'auth_tokens', 'email_otps', 'password_reset_otps'];
    $missingTables = [];

    foreach ($requiredTables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() === 0) {
            $missingTables[] = $table;
        }
    }

    if (!empty($missingTables)) {
        // Tables are missing - try to create them
        $schemaFile = __DIR__ . '/../database/schema.sql';
        if (file_exists($schemaFile)) {
            $sql = file_get_contents($schemaFile);
            $pdo->exec($sql);
            error_log("Database schema created successfully");
        } else {
            throw new Exception("Database schema file not found at: $schemaFile");
        }
    }
    
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    if (strpos($e->getMessage(), "Unknown database") !== false) {
        try {
            // Try to create the database
            $pdo = new PDO(
                "mysql:host=$host",
                $username,
                $password
            );
            $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname");
            error_log("Database created successfully");
            
            // Reconnect to the new database
            $pdo = new PDO(
                "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
                ]
            );
            
            // Create tables
            $schemaFile = __DIR__ . '/../database/schema.sql';
            if (file_exists($schemaFile)) {
                $sql = file_get_contents($schemaFile);
                $pdo->exec($sql);
                error_log("Database schema created successfully");
            } else {
                throw new Exception("Database schema file not found");
            }
        } catch (PDOException $e2) {
            error_log("Failed to create database: " . $e2->getMessage());
            throw new Exception("Failed to initialize database. Please check your MySQL configuration.");
        }
    } else {
        throw new Exception("Database connection failed. Please check your configuration.");
    }
} 