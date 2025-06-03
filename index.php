<?php
require_once 'config/database.php';
require_once 'includes/Utils.php';

session_start();

// Get base URL dynamically
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
$baseUrl = $protocol . $host;

// API version
$apiVersion = "1.0.0";

// Check if developer is logged in
$isLoggedIn = isset($_SESSION['dev_id']);
$developerData = null;

if ($isLoggedIn) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM developers WHERE id = ?");
        $stmt->execute([$_SESSION['dev_id']]);
        $developerData = $stmt->fetch();
    } catch (Exception $e) {
        error_log($e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Authentication API - Developer Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#2c3e50',
                        secondary: '#3498db',
                        accent: '#e74c3c',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col font-['Poppins']">
    <!-- Navigation -->
    <nav class="bg-primary shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <a href="#" class="text-white text-xl font-bold">User Auth API</a>
                    </div>
                    <div class="hidden sm:ml-6 sm:flex justify-center items-center sm:space-x-8">
                        <a href="#documentation" class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Documentation</a>
                        <?php if ($isLoggedIn): ?>
                        <a href="#dashboard" class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Dashboard</a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="hidden sm:ml-6 sm:flex sm:items-center">
                    <?php if ($isLoggedIn): ?>
                    <a href="dev/logout.php" class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Logout</a>
                    <?php else: ?>
                    <a href="dev/login-page.php" class="ml-4 bg-white hover:bg-gray-100 text-primary px-4 py-2 rounded-md text-sm font-medium">Login</a>
                    <a href="dev/register-page.php" class="ml-4 bg-white hover:bg-gray-100 text-primary px-4 py-2 rounded-md text-sm font-medium">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="bg-gradient-to-r from-primary to-secondary text-white py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h1 class="text-4xl font-extrabold sm:text-5xl md:text-6xl">User Authentication API</h1>
                <p class="mt-3 max-w-md mx-auto text-xl text-gray-100 sm:text-2xl md:mt-5 md:max-w-3xl">
                    A secure, scalable API for managing user authentication in your applications.
                </p>
                <?php if (!$isLoggedIn): ?>
                <div class="mt-5 max-w-md mx-auto sm:flex sm:justify-center md:mt-8">
                    <div class="rounded-md shadow">
                        <a href="/dev/register-page.php" class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-primary bg-white hover:bg-gray-50 md:py-4 md:text-lg md:px-10">
                            Get Started
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <?php if ($isLoggedIn && $developerData): ?>
        <!-- Developer Dashboard -->
        <section id="dashboard" class="mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-8">Developer Dashboard</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="md:col-span-2 space-y-6">
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-xl font-semibold text-gray-900 mb-4">Account Details</h3>
                        <div class="space-y-3">
                            <p class="text-gray-600"><span class="font-medium">Name:</span> <?php echo htmlspecialchars($developerData['name']); ?></p>
                            <p class="text-gray-600"><span class="font-medium">Email:</span> <?php echo htmlspecialchars($developerData['email']); ?></p>
                            <p class="text-gray-600"><span class="font-medium">Account Created:</span> <?php echo date('F j, Y', strtotime($developerData['created_at'])); ?></p>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-xl font-semibold text-gray-900 mb-4">API Key</h3>
                        <div class="flex items-center justify-between bg-gray-50 p-4 rounded-lg">
                            <code id="apiKey" class="text-sm font-mono text-gray-800"><?php echo htmlspecialchars($developerData['api_key']); ?></code>
                            <button onclick="copyApiKey()" class="ml-4 text-gray-500 hover:text-gray-700">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                        <p class="mt-2 text-sm text-gray-500">Keep this key secure and never share it publicly.</p>
                    </div>
                </div>
                <div class="space-y-6">
                    <div class="bg-white rounded-lg shadow-md p-6 text-center">
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">API Calls</h3>
                        <p class="text-4xl font-bold text-primary">1,234</p>
                        <p class="text-sm text-gray-500">Last 30 days</p>
                    </div>
                    <div class="bg-white rounded-lg shadow-md p-6 text-center">
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Active Users</h3>
                        <p class="text-4xl font-bold text-primary">56</p>
                        <p class="text-sm text-gray-500">Total registered users</p>
                    </div>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- Documentation Section -->
        <section id="documentation" class="space-y-8">
            <div class="flex flex-col md:flex-row gap-8">
                <!-- Table of Contents -->
                <div class="md:w-1/4">
                    <div class="sticky top-4 bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Table of Contents</h3>
                        <nav class="space-y-2">
                            <a href="#overview" class="block text-gray-600 hover:text-primary hover:bg-gray-50 px-3 py-2 rounded-md">Overview</a>
                            <a href="#developer-portal" class="block text-gray-600 hover:text-primary hover:bg-gray-50 px-3 py-2 rounded-md">Developer Portal</a>
                            <a href="#authentication" class="block text-gray-600 hover:text-primary hover:bg-gray-50 px-3 py-2 rounded-md">Authentication</a>
                            <a href="#endpoints" class="block text-gray-600 hover:text-primary hover:bg-gray-50 px-3 py-2 rounded-md">API Endpoints</a>
                            <a href="#error-responses" class="block text-gray-600 hover:text-primary hover:bg-gray-50 px-3 py-2 rounded-md">Error Responses</a>
                            <a href="#security" class="block text-gray-600 hover:text-primary hover:bg-gray-50 px-3 py-2 rounded-md">Security Best Practices</a>
                        </nav>
                    </div>
                </div>

                <!-- Main Documentation Content -->
                <div class="md:w-3/4 space-y-8">
                    <!-- Overview Section -->
                    <div id="overview" class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-3xl font-bold text-gray-900 mb-4">Overview</h2>
                        <p class="text-gray-600 mb-6">Welcome to the User Authentication API documentation. This system provides both a developer portal for managing API keys and a complete authentication API that can be integrated into any application.</p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">Developer Portal Features</h3>
                                <ul class="list-disc list-inside text-gray-600 space-y-2">
                                    <li>Developer registration with email verification</li>
                                    <li>Secure login system</li>
                                    <li>API key management</li>
                                    <li>Interactive documentation</li>
                                    <li>Modern UI with Tailwind CSS</li>
                                </ul>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">API Features</h3>
                                <ul class="list-disc list-inside text-gray-600 space-y-2">
                                    <li>User registration with email verification</li>
                                    <li>Secure token-based authentication</li>
                                    <li>Password reset functionality</li>
                                    <li>Password change functionality</li>
                                    <li>Secure token management</li>
                                    <li>Rate limiting and API key validation</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Developer Portal Section -->
                    <div id="developer-portal" class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-3xl font-bold text-gray-900 mb-4">Developer Portal</h2>
                        <p class="text-gray-600 mb-6">The developer portal provides endpoints for managing your developer account and API keys.</p>

                        <!-- Register Developer -->
                        <div class="mb-8">
                            <div class="flex items-center mb-4">
                                <span class="bg-green-500 text-white px-3 py-1 rounded-md text-sm font-medium mr-3">POST</span>
                                <h4 class="text-xl font-semibold text-gray-900">/dev/register.php</h4>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <pre class="text-sm font-mono text-gray-800"><span class="text-purple-600">{</span>
    <span class="text-blue-600">"email"</span>: <span class="text-green-600">"dev@example.com"</span>,
    <span class="text-blue-600">"password"</span>: <span class="text-green-600">"SecurePass123"</span>,
    <span class="text-blue-600">"fullName"</span>: <span class="text-green-600">"John Doe"</span>,
    <span class="text-blue-600">"systemName"</span>: <span class="text-green-600">"My App"</span>
<span class="text-purple-600">}</span></pre>
                            </div>
                        </div>

                        <!-- Developer Login -->
                        <div class="mb-8">
                            <div class="flex items-center mb-4">
                                <span class="bg-green-500 text-white px-3 py-1 rounded-md text-sm font-medium mr-3">POST</span>
                                <h4 class="text-xl font-semibold text-gray-900">/dev/login.php</h4>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <pre class="text-sm font-mono text-gray-800"><span class="text-purple-600">{</span>
    <span class="text-blue-600">"email"</span>: <span class="text-green-600">"dev@example.com"</span>,
    <span class="text-blue-600">"password"</span>: <span class="text-green-600">"SecurePass123"</span>
<span class="text-purple-600">}</span></pre>
                            </div>
                        </div>
                    </div>

                    <!-- Authentication Section -->
                    <div id="authentication" class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-3xl font-bold text-gray-900 mb-4">Authentication</h2>
                        <p class="text-gray-600 mb-4">All API requests require authentication using your API key in the header:</p>
                        <div class="bg-gray-50 p-4 rounded-lg mb-4">
                            <div class="flex items-center justify-between">
                                <code class="text-sm font-mono text-gray-800">X-API-Key: your_api_key</code>
                                <button onclick="copyToClipboard('X-API-Key: your_api_key')" class="text-gray-500 hover:text-gray-700">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="mt-4 bg-yellow-50 border-l-4 border-yellow-400 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700">
                                        <strong>Note:</strong> Keep your API key secure and never expose it in client-side code.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Endpoints Section -->
                    <div id="endpoints" class="space-y-6">
                        <h2 class="text-3xl font-bold text-gray-900 mb-6">API Endpoints</h2>
                        
                        <!-- Register User -->
                        <div class="bg-white rounded-lg shadow-md p-6">
                            <div class="flex items-center mb-4">
                                <span class="bg-green-500 text-white px-3 py-1 rounded-md text-sm font-medium mr-3">POST</span>
                                <h4 class="text-xl font-semibold text-gray-900">/api/register.php</h4>
                            </div>
                            <p class="text-gray-600 mb-4">Register a new user account.</p>
                            
                            <div class="space-y-4">
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <pre class="text-sm font-mono text-gray-800"><span class="text-purple-600">{</span>
    <span class="text-blue-600">"email"</span>: <span class="text-green-600">"user@example.com"</span>,
    <span class="text-blue-600">"password"</span>: <span class="text-green-600">"SecurePass123"</span>
<span class="text-purple-600">}</span></pre>
                                </div>
                            </div>
                        </div>

                        <!-- Request OTP -->
                        <div class="bg-white rounded-lg shadow-md p-6">
                            <div class="flex items-center mb-4">
                                <span class="bg-green-500 text-white px-3 py-1 rounded-md text-sm font-medium mr-3">POST</span>
                                <h4 class="text-xl font-semibold text-gray-900">/api/request-otp.php</h4>
                            </div>
                            <p class="text-gray-600 mb-4">Request an OTP for email verification or password reset.</p>
                            
                            <div class="space-y-4">
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <pre class="text-sm font-mono text-gray-800"><span class="text-purple-600">{</span>
    <span class="text-blue-600">"email"</span>: <span class="text-green-600">"user@example.com"</span>,
    <span class="text-blue-600">"purpose"</span>: <span class="text-green-600">"email-verification"</span>
<span class="text-purple-600">}</span></pre>
                                </div>
                            </div>
                        </div>

                        <!-- Verify Email -->
                        <div class="bg-white rounded-lg shadow-md p-6">
                            <div class="flex items-center mb-4">
                                <span class="bg-green-500 text-white px-3 py-1 rounded-md text-sm font-medium mr-3">POST</span>
                                <h4 class="text-xl font-semibold text-gray-900">/api/verify-email.php</h4>
                            </div>
                            <p class="text-gray-600 mb-4">Verify user's email using OTP.</p>
                            
                            <div class="space-y-4">
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <pre class="text-sm font-mono text-gray-800"><span class="text-purple-600">{</span>
    <span class="text-blue-600">"email"</span>: <span class="text-green-600">"user@example.com"</span>,
    <span class="text-blue-600">"otp"</span>: <span class="text-green-600">"123456"</span>
<span class="text-purple-600">}</span></pre>
                                </div>
                            </div>
                        </div>

                        <!-- User Login -->
                        <div class="bg-white rounded-lg shadow-md p-6">
                            <div class="flex items-center mb-4">
                                <span class="bg-green-500 text-white px-3 py-1 rounded-md text-sm font-medium mr-3">POST</span>
                                <h4 class="text-xl font-semibold text-gray-900">/api/login.php</h4>
                            </div>
                            <p class="text-gray-600 mb-4">Authenticate a user and get an access token.</p>
                            
                            <div class="space-y-4">
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <pre class="text-sm font-mono text-gray-800"><span class="text-purple-600">{</span>
    <span class="text-blue-600">"email"</span>: <span class="text-green-600">"user@example.com"</span>,
    <span class="text-blue-600">"password"</span>: <span class="text-green-600">"SecurePass123"</span>
<span class="text-purple-600">}</span></pre>
                                </div>
                            </div>
                        </div>

                        <!-- Reset Password -->
                        <div class="bg-white rounded-lg shadow-md p-6">
                            <div class="flex items-center mb-4">
                                <span class="bg-green-500 text-white px-3 py-1 rounded-md text-sm font-medium mr-3">POST</span>
                                <h4 class="text-xl font-semibold text-gray-900">/api/reset-password.php</h4>
                            </div>
                            <p class="text-gray-600 mb-4">Reset user's password using OTP.</p>
                            
                            <div class="space-y-4">
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <pre class="text-sm font-mono text-gray-800"><span class="text-purple-600">{</span>
    <span class="text-blue-600">"otp"</span>: <span class="text-green-600">"123456"</span>,
    <span class="text-blue-600">"new_password"</span>: <span class="text-green-600">"NewSecurePass123"</span>
<span class="text-purple-600">}</span></pre>
                                </div>
                            </div>
                        </div>

                        <!-- Change Password -->
                        <div class="bg-white rounded-lg shadow-md p-6">
                            <div class="flex items-center mb-4">
                                <span class="bg-green-500 text-white px-3 py-1 rounded-md text-sm font-medium mr-3">POST</span>
                                <h4 class="text-xl font-semibold text-gray-900">/api/change-password.php</h4>
                            </div>
                            <p class="text-gray-600 mb-4">Change user's password (requires authentication).</p>
                            
                            <div class="space-y-4">
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <pre class="text-sm font-mono text-gray-800"><span class="text-purple-600">{</span>
    <span class="text-blue-600">"old_password"</span>: <span class="text-green-600">"CurrentPass123"</span>,
    <span class="text-blue-600">"new_password"</span>: <span class="text-green-600">"NewSecurePass123"</span>,
    <span class="text-blue-600">"confirm_password"</span>: <span class="text-green-600">"NewSecurePass123"</span>
<span class="text-purple-600">}</span></pre>
                                </div>
                            </div>
                        </div>

                        <!-- Logout -->
                        <div class="bg-white rounded-lg shadow-md p-6">
                            <div class="flex items-center mb-4">
                                <span class="bg-green-500 text-white px-3 py-1 rounded-md text-sm font-medium mr-3">POST</span>
                                <h4 class="text-xl font-semibold text-gray-900">/api/logout.php</h4>
                            </div>
                            <p class="text-gray-600 mb-4">Invalidate the current authentication token.</p>
                        </div>
                    </div>

                    <!-- Error Responses Section -->
                    <div id="error-responses" class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-3xl font-bold text-gray-900 mb-4">Error Responses</h2>
                        <p class="text-gray-600 mb-6">All API endpoints return consistent error responses in the following format:</p>

                        <div class="space-y-6">
                            <!-- General Error Format -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">General Error Format</h3>
                                <pre class="text-sm font-mono text-gray-800"><span class="text-purple-600">{</span>
    <span class="text-blue-600">"success"</span>: <span class="text-orange-600">false</span>,
    <span class="text-blue-600">"error"</span>: <span class="text-green-600">"error_type"</span>,
    <span class="text-blue-600">"message"</span>: <span class="text-green-600">"Human-readable error message"</span>
<span class="text-purple-600">}</span></pre>
                            </div>

                            <!-- Common Error Types -->
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Common Error Types</h3>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status Code</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Error Type</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">400</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Invalid request</td>
                                                <td class="px-6 py-4 text-sm text-gray-500">Missing or invalid parameters</td>
                                            </tr>
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">401</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Unauthorized</td>
                                                <td class="px-6 py-4 text-sm text-gray-500">Invalid or missing API key</td>
                                            </tr>
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">403</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Forbidden</td>
                                                <td class="px-6 py-4 text-sm text-gray-500">Invalid authentication token</td>
                                            </tr>
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">404</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Not found</td>
                                                <td class="px-6 py-4 text-sm text-gray-500">Resource not found</td>
                                            </tr>
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">429</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Too many requests</td>
                                                <td class="px-6 py-4 text-sm text-gray-500">Rate limit exceeded</td>
                                            </tr>
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">500</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Server error</td>
                                                <td class="px-6 py-4 text-sm text-gray-500">Internal server error</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Example Error Responses -->
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Example Error Responses</h3>
                                
                                <!-- Invalid Request -->
                                <div class="mb-6">
                                    <h4 class="text-md font-medium text-gray-900 mb-2">Invalid Request</h4>
                                    <div class="bg-gray-50 p-4 rounded-lg">
                                        <pre class="text-sm font-mono text-gray-800"><span class="text-purple-600">{</span>
    <span class="text-blue-600">"success"</span>: <span class="text-orange-600">false</span>,
    <span class="text-blue-600">"error"</span>: <span class="text-green-600">"Invalid request"</span>,
    <span class="text-blue-600">"message"</span>: <span class="text-green-600">"No matching user found with the provided email and token"</span>
<span class="text-purple-600">}</span></pre>
                                    </div>
                                </div>

                                <!-- Token Expired -->
                                <div class="mb-6">
                                    <h4 class="text-md font-medium text-gray-900 mb-2">Token Expired</h4>
                                    <div class="bg-gray-50 p-4 rounded-lg">
                                        <pre class="text-sm font-mono text-gray-800"><span class="text-purple-600">{</span>
    <span class="text-blue-600">"success"</span>: <span class="text-orange-600">false</span>,
    <span class="text-blue-600">"error"</span>: <span class="text-green-600">"Token expired"</span>,
    <span class="text-blue-600">"message"</span>: <span class="text-green-600">"The authentication token has expired"</span>,
    <span class="text-blue-600">"expired_at"</span>: <span class="text-green-600">"2024-03-21 12:34:56"</span>
<span class="text-purple-600">}</span></pre>
                                    </div>
                                </div>

                                <!-- Rate Limit Exceeded -->
                                <div>
                                    <h4 class="text-md font-medium text-gray-900 mb-2">Rate Limit Exceeded</h4>
                                    <div class="bg-gray-50 p-4 rounded-lg">
                                        <pre class="text-sm font-mono text-gray-800"><span class="text-purple-600">{</span>
    <span class="text-blue-600">"success"</span>: <span class="text-orange-600">false</span>,
    <span class="text-blue-600">"error"</span>: <span class="text-green-600">"Rate limit exceeded"</span>,
    <span class="text-blue-600">"message"</span>: <span class="text-green-600">"Too many requests. Please try again later."</span>,
    <span class="text-blue-600">"retry_after"</span>: <span class="text-orange-600">60</span>
<span class="text-purple-600">}</span></pre>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Security Section -->
                    <div id="security" class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-3xl font-bold text-gray-900 mb-4">Security Best Practices</h2>
                        <div class="space-y-4">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">API Key Security</h3>
                                <ul class="list-disc list-inside text-gray-600 space-y-2">
                                    <li>Never expose your API key in client-side code</li>
                                    <li>Store API keys securely in environment variables</li>
                                    <li>Rotate API keys periodically</li>
                                    <li>Use HTTPS for all API requests</li>
                                </ul>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">Password Security</h3>
                                <ul class="list-disc list-inside text-gray-600 space-y-2">
                                    <li>Use strong passwords (minimum 8 characters, mix of letters, numbers, and symbols)</li>
                                    <li>Implement rate limiting for login attempts</li>
                                    <li>Use secure password reset flows with time-limited tokens</li>
                                    <li>Enable two-factor authentication when available</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Footer -->
    <footer class="bg-primary text-white mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <h5 class="text-xl font-bold mb-2">User Authentication API</h5>
                    <p class="text-gray-300">Version <?php echo $apiVersion; ?></p>
                </div>
                <div class="text-right">
                    <p class="text-gray-300">Need help? <a href="mailto:support@example.com" class="text-white hover:text-gray-200">Contact Support</a></p>
                </div>
            </div>
        </div>
    </footer>

    <script>
        function copyApiKey() {
            const apiKey = document.getElementById('apiKey');
            navigator.clipboard.writeText(apiKey.textContent)
                .then(() => {
                    alert('API key copied to clipboard!');
                })
                .catch(err => {
                    console.error('Failed to copy API key:', err);
                });
        }

        function copyToClipboard(text) {
            navigator.clipboard.writeText(text)
                .then(() => {
                    const button = event.currentTarget;
                    const originalIcon = button.innerHTML;
                    button.innerHTML = '<i class="fas fa-check text-green-500"></i>';
                    setTimeout(() => {
                        button.innerHTML = originalIcon;
                    }, 2000);
                })
                .catch(err => {
                    console.error('Failed to copy text:', err);
                    const button = event.currentTarget;
                    const originalIcon = button.innerHTML;
                    button.innerHTML = '<i class="fas fa-times text-red-500"></i>';
                    setTimeout(() => {
                        button.innerHTML = originalIcon;
                    }, 2000);
                });
        }

        // Store JSON strings in variables using template literals
        const registerRequest = `{
    "email": "user@example.com",
    "password": "SecurePass123",
    "name": "John Doe"
}`;

        const registerResponse = `{
    "success": true,
    "message": "User registered successfully",
    "data": {
        "user_id": 123,
        "email": "user@example.com"
    }
}`;

        const otpRequest = `{
    "email": "user@example.com",
    "purpose": "email-verification"
}`;

        const otpResponse = `{
    "success": true,
    "message": "OTP generated successfully",
    "data": {
        "user_id": 123,
        "email": "user@example.com",
        "purpose": "email-verification",
        "otp": "123456",
        "otp_expires_at": "2024-03-21 12:34:56",
        "auth_token": "your_auth_token",
        "token_expires_at": "2024-03-22 10:30:00"
    }
}`;

        const verifyEmailRequest = `{
    "email": "user@example.com",
    "otp": "123456"
}`;

        const verifyEmailResponse = `{
    "success": true,
    "message": "Email verified successfully",
    "data": {
        "user_id": 123,
        "email": "user@example.com",
        "auth_token": "new_auth_token",
        "expires_at": "2024-03-22 10:30:00"
    }
}`;

        const resetPasswordRequest = `{
    "otp": "123456",
    "new_password": "NewSecurePass123"
}`;

        const resetPasswordResponse = `{
    "success": true,
    "message": "Password reset successfully",
    "data": {
        "email": "user@example.com",
        "token_expires_at": "2024-03-22 10:30:00"
    }
}`;

        const refreshTokenResponse = `{
    "success": true,
    "message": "Token refreshed successfully",
    "data": {
        "token": "new_auth_token",
        "expires_at": "2024-03-22 10:30:00"
    }
}`;

        const deleteUserRequest = `{
    "email": "user@example.com"
}`;

        const deleteUserResponse = `{
    "success": true,
    "message": "User account deleted successfully",
    "data": {
        "email": "user@example.com"
    }
}`;

        const invalidRequestError = `{
    "success": false,
    "error": "Invalid request",
    "message": "No matching user found with the provided email and token"
}`;

        const tokenExpiredError = `{
    "success": false,
    "error": "Token expired",
    "message": "The authentication token has expired",
    "expired_at": "2024-03-21 12:34:56"
}`;

        const errorResponse = `{
    "success": false,
    "error": "error_type",
    "message": "Human-readable error message"
}`;

        // Store header strings
        const headers = {
            basic: "Content-Type: application/json\nX-API-Key: your_api_key",
            withAuth: "Content-Type: application/json\nX-API-Key: your_api_key\nAuthorization: Bearer auth_token",
            withCurrentAuth: "Content-Type: application/json\nX-API-Key: your_api_key\nAuthorization: Bearer current_auth_token"
        };
    </script>
</body>
</html> 