<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/Utils.php';

function verifyApiKey() {
    global $pdo;

    // Check for API key
    $headers = getallheaders();
    $apiKey = $headers['X-API-Key'] ?? $headers['x-api-key'] ?? $headers['X-Api-Key'] ?? '';

    if (!$apiKey) {
        Utils::sendJsonResponse(['error' => "API key is required"], 401);
    }

    try {
        // Verify API key and get client info
        $stmt = $pdo->prepare("
            SELECT ac.id as client_id, ac.dev_id, da.is_email_verified
            FROM api_clients ac
            JOIN dev_accounts da ON da.id = ac.dev_id
            WHERE ac.api_key = ? AND ac.is_active = TRUE
        ");
        $stmt->execute([$apiKey]);
        $client = $stmt->fetch();

        if (!$client) {
            Utils::sendJsonResponse(['error' => 'Invalid API key'], 401);
        }

        if (!$client['is_email_verified']) {
            Utils::sendJsonResponse(['error' => 'Developer account not verified'], 403);
        }

        // Return client info for use in API endpoints
        return [
            'client_id' => $client['client_id'],
            'dev_id' => $client['dev_id']
        ];

    } catch (Exception $e) {
        error_log($e->getMessage());
        Utils::sendJsonResponse(['error' => 'Failed to verify API key'], 500);
    }
} 