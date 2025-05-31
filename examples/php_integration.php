<?php
class UserAuthAPI {
    private $apiKey;
    private $baseUrl = 'https://morales.infy.uk/user_auth_api1';
    private $authToken;

    public function __construct($apiKey) {
        $this->apiKey = $apiKey;
    }

    private function sendRequest($endpoint, $data, $includeToken = false) {
        $ch = curl_init($this->baseUrl . $endpoint);
        
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
            'X-API-Key: ' . $this->apiKey
        ];

        if ($includeToken && $this->authToken) {
            $headers[] = 'Authorization: Bearer ' . $this->authToken;
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'status' => $httpCode,
            'data' => json_decode($response, true)
        ];
    }

    // New method for token validation
    public function validateToken() {
        return $this->sendRequest('/api/validate-token.php', [], true);
    }

    // ... (rest of the existing methods)
}

// Usage Example:
$auth = new UserAuthAPI('your_api_key_here');

// Login first to get a token
$loginResponse = $auth->login('user@example.com', 'password123');

// Then validate the token
if ($loginResponse['status'] === 200) {
    $validationResponse = $auth->validateToken();
    if ($validationResponse['status'] === 200) {
        echo "Token is valid. User: " . $validationResponse['data']['data']['email'];
    } else {
        echo "Token validation failed: " . $validationResponse['data']['message'];
    }
} 