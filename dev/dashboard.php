<?php
require_once '../config/database.php';
require_once '../includes/Utils.php';

// Check for auth token
$headers = getallheaders();
$token = $headers['Authorization'] ?? '';

if (!$token || !str_starts_with($token, 'Bearer ')) {
    header('Location: /index.php');
    exit;
}

$token = substr($token, 7);

// Verify JWT token
if (!Utils::verifyJWT($token, getenv('JWT_SECRET') ?: 'your-secret-key')) {
    header('Location: /index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Developer Dashboard - Auth API System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-6xl mx-auto px-4">
            <div class="flex justify-between">
                <div class="flex space-x-7">
                    <div>
                        <a href="#" class="flex items-center py-4">
                            <span class="font-semibold text-gray-500 text-lg">Auth API System</span>
                        </a>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <span class="text-gray-500" id="userEmail"></span>
                    <button class="py-2 px-4 bg-red-500 text-white rounded hover:bg-red-600" id="logoutBtn">Logout</button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-6xl mx-auto px-4 py-8">
        <!-- API Keys Section -->
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <h2 class="text-2xl font-bold mb-6">Your API Keys</h2>
            <div id="apiKeysContainer" class="space-y-4">
                <!-- API keys will be populated here -->
            </div>
            <button class="mt-6 bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600" id="newApiKeyBtn">
                Generate New API Key
            </button>
        </div>

        <!-- Documentation Section -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-2xl font-bold mb-6">API Documentation</h2>
            <div class="space-y-6">
                <div>
                    <h3 class="text-xl font-semibold mb-4">Authentication</h3>
                    <p class="text-gray-600 mb-4">
                        Include your API key in the X-API-Key header for all requests:
                    </p>
                    <pre class="bg-gray-100 p-4 rounded overflow-x-auto">
X-API-Key: your-api-key</pre>
                </div>

                <div>
                    <h3 class="text-xl font-semibold mb-4">Endpoints</h3>
                    <div class="space-y-4">
                        <div>
                            <h4 class="font-semibold mb-2">Register User</h4>
                            <pre class="bg-gray-100 p-4 rounded overflow-x-auto">
POST /api/register.php
{
    "email": "user@example.com",
    "password": "securePassword123"
}</pre>
                        </div>

                        <div>
                            <h4 class="font-semibold mb-2">Verify Email</h4>
                            <pre class="bg-gray-100 p-4 rounded overflow-x-auto">
POST /api/verify-email.php
{
    "email": "user@example.com",
    "otp": "123456"
}</pre>
                        </div>

                        <div>
                            <h4 class="font-semibold mb-2">Login</h4>
                            <pre class="bg-gray-100 p-4 rounded overflow-x-auto">
POST /api/login.php
{
    "email": "user@example.com",
    "password": "securePassword123"
}</pre>
                        </div>

                        <div>
                            <h4 class="font-semibold mb-2">Reset Password</h4>
                            <pre class="bg-gray-100 p-4 rounded overflow-x-auto">
POST /api/reset-password.php
{
    "email": "user@example.com"
}</pre>
                        </div>

                        <div>
                            <h4 class="font-semibold mb-2">Change Password</h4>
                            <pre class="bg-gray-100 p-4 rounded overflow-x-auto">
POST /api/change-password.php
{
    "current_password": "oldPassword123",
    "new_password": "newPassword123"
}</pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Get user data from token
        const token = localStorage.getItem('authToken');
        if (!token) {
            window.location.href = '/index.php';
        }

        // Display user email
        const payload = JSON.parse(atob(token.split('.')[1]));
        document.getElementById('userEmail').textContent = payload.email;

        // Logout handler
        document.getElementById('logoutBtn').addEventListener('click', () => {
            localStorage.removeItem('authToken');
            window.location.href = '/index.php';
        });

        // Load API keys
        async function loadApiKeys() {
            try {
                const response = await fetch('/dev/api-keys.php', {
                    headers: {
                        'Authorization': `Bearer ${token}`
                    }
                });
                const data = await response.json();
                
                const container = document.getElementById('apiKeysContainer');
                container.innerHTML = data.api_clients.map(client => `
                    <div class="border p-4 rounded">
                        <div class="flex justify-between items-center">
                            <div>
                                <h4 class="font-semibold">${client.system_name}</h4>
                                <p class="text-gray-600 mt-2">API Key: ${client.api_key}</p>
                            </div>
                            <button class="regenerateKey bg-yellow-500 text-white py-2 px-4 rounded hover:bg-yellow-600"
                                    data-client-id="${client.id}">
                                Regenerate
                            </button>
                        </div>
                    </div>
                `).join('');

                // Add regenerate handlers
                document.querySelectorAll('.regenerateKey').forEach(btn => {
                    btn.addEventListener('click', async () => {
                        if (confirm('Are you sure? This will invalidate the current API key.')) {
                            try {
                                const response = await fetch('/dev/regenerate-key.php', {
                                    method: 'POST',
                                    headers: {
                                        'Authorization': `Bearer ${token}`,
                                        'Content-Type': 'application/json'
                                    },
                                    body: JSON.stringify({
                                        client_id: btn.dataset.clientId
                                    })
                                });
                                
                                if (response.ok) {
                                    loadApiKeys();
                                } else {
                                    alert('Failed to regenerate API key');
                                }
                            } catch (error) {
                                alert('An error occurred');
                            }
                        }
                    });
                });
            } catch (error) {
                alert('Failed to load API keys');
            }
        }

        // New API key handler
        document.getElementById('newApiKeyBtn').addEventListener('click', async () => {
            const systemName = prompt('Enter system name:');
            if (systemName) {
                try {
                    const response = await fetch('/dev/new-api-key.php', {
                        method: 'POST',
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ system_name: systemName })
                    });
                    
                    if (response.ok) {
                        loadApiKeys();
                    } else {
                        alert('Failed to create new API key');
                    }
                } catch (error) {
                    alert('An error occurred');
                }
            }
        });

        // Initial load
        loadApiKeys();
    </script>
</body>
</html> 