<?php
// Prevent any HTML error output
ini_set('display_errors', 0);
ini_set('html_errors', 0);
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    require_once 'config/database.php';

    // Drop existing tables if they exists
    $pdo->exec("DROP TABLE IF EXISTS api_clients");
    $pdo->exec("DROP TABLE IF EXISTS dev_accounts");

    // SQL to create dev_accounts table
    $sql_dev_accounts = "
    CREATE TABLE dev_accounts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        full_name VARCHAR(255) NOT NULL,
        is_email_verified BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    // SQL to create api_clients table
    $sql_api_clients = "
    CREATE TABLE api_clients (
        id INT AUTO_INCREMENT PRIMARY KEY,
        dev_id INT NOT NULL,
        system_name VARCHAR(255) NOT NULL,
        api_key VARCHAR(255) NOT NULL UNIQUE,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (dev_id) REFERENCES dev_accounts(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    // Execute the CREATE TABLE statements
    $pdo->exec($sql_dev_accounts);
    $pdo->exec($sql_api_clients);

    // Verify tables were created
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $required_tables = ['dev_accounts', 'api_clients'];
    $missing_tables = array_diff($required_tables, array_map('strtolower', $tables));

    if (!empty($missing_tables)) {
        throw new Exception("Failed to create tables: " . implode(', ', $missing_tables));
    }

    // Verify table structures
    $table_info = [];
    foreach ($required_tables as $table) {
        $columns = $pdo->query("DESCRIBE $table")->fetchAll(PDO::FETCH_COLUMN);
        $table_info[$table] = $columns;
    }

    echo json_encode([
        'success' => true,
        'message' => 'Database tables created/verified successfully',
        'tables' => $table_info
    ]);

} catch (Exception $e) {
    error_log("Setup database error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 