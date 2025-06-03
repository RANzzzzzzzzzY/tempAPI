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
                    <a href="dev/login-page.php" class="ml-4 bg-secondary hover:bg-sky-400 text-white px-4 py-2 rounded-md text-sm font-medium">Login</a>
                    <a href="dev/register-page.php" class="ml-4 bg-secondary hover:bg-sky-400 text-white px-4 py-2 rounded-md text-sm font-medium">Register</a>
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
                        <a href="user_authentication_api/dev/register-page.php" class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-primary bg-white hover:bg-gray-50 md:py-4 md:text-lg md:px-10">
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
                            <a href="#authentication" class="block text-gray-600 hover:text-primary hover:bg-gray-50 px-3 py-2 rounded-md">Authentication</a>
                            <a href="#endpoints" class="block text-gray-600 hover:text-primary hover:bg-gray-50 px-3 py-2 rounded-md">API Endpoints</a>
                            <a href="#error-responses" class="block text-gray-600 hover:text-primary hover:bg-gray-50 px-3 py-2 rounded-md">Error Responses</a>
                            <a href="#integration" class="block text-gray-600 hover:text-primary hover:bg-gray-50 px-3 py-2 rounded-md">Integration Guide</a>
                            <a href="#security" class="block text-gray-600 hover:text-primary hover:bg-gray-50 px-3 py-2 rounded-md">Security Best Practices</a>
                        </nav>
                    </div>
                </div>

                <!-- Main Documentation Content -->
                <div class="md:w-3/4 space-y-8">
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-3xl font-bold text-gray-900 mb-8">API Documentation</h2>
                        <p class="text-gray-600 mb-6">Welcome to the User Authentication API documentation. This guide will help you integrate our authentication system into your application.</p>
                    </div>
                    
                    <!-- Authentication Section -->
                    <div id="authentication" class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center mb-4">
                            <i class="fas fa-key text-primary text-2xl mr-3"></i>
                            <h3 class="text-2xl font-semibold text-gray-900">Authentication</h3>
                        </div>
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
                        <h3 class="text-2xl font-bold text-gray-900 mb-6">API Endpoints</h3>
                        
                        <!-- Register User -->
                        <div class="bg-white rounded-lg shadow-md p-6">
                            <div class="flex items-center mb-4">
                                <span class="bg-green-500 text-white px-3 py-1 rounded-md text-sm font-medium mr-3">POST</span>
                                <h4 class="text-xl font-semibold text-gray-900">/api/register.php</h4>
                            </div>
                            <p class="text-gray-600 mb-4">Register a new user account.</p>
                            
                            <div class="space-y-4">
                                <!-- Headers Section -->
                                <div>
                                    <h5 class="text-lg font-medium text-gray-900 mb-2 flex items-center">
                                        <i class="fas fa-heading text-primary mr-2"></i>
                                        Headers
                                    </h5>
                                    <div class="bg-gray-50 p-4 rounded-lg">
                                        <div class="flex items-center justify-between">
                                            <div class="space-y-2">
                                                <div class="flex items-center">
                                                    <span class="text-green-600 font-semibold mr-2">Content-Type:</span>
                                                    <code class="text-sm font-mono text-gray-800">application/json</code>
                                                </div>
                                                <div class="flex items-center">
                                                    <span class="text-green-600 font-semibold mr-2">X-API-Key:</span>
                                                    <code class="text-sm font-mono text-gray-800">your_api_key</code>
                                                </div>
                                            </div>
                                            <button onclick="copyToClipboard('Content-Type: application/json\nX-API-Key: your_api_key')" 
                                                    class="text-gray-500 hover:text-gray-700 transition-colors duration-200">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Request Body Section -->
                                <div>
                                    <h5 class="text-lg font-medium text-gray-900 mb-2 flex items-center">
                                        <i class="fas fa-code text-primary mr-2"></i>
                                        Request Body
                                    </h5>
                                    <div class="bg-gray-50 p-4 rounded-lg">
                                        <div class="flex items-center justify-between">
                                            <div class="w-full">
                                                <pre class="text-sm font-mono text-gray-800"><span class="text-purple-600">{</span>
    <span class="text-blue-600">"email"</span>: <span class="text-green-600">"user@example.com"</span>,
    <span class="text-blue-600">"password"</span>: <span class="text-green-600">"SecurePass123"</span>,
    <span class="text-blue-600">"name"</span>: <span class="text-green-600">"John Doe"</span>
<span class="text-purple-600">}</span></pre>
                                            </div>
                                            <button onclick="copyToClipboard(registerRequest)" class="text-gray-500 hover:text-gray-700 transition-colors duration-200 ml-4">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Response Section -->
                                <div>
                                    <h5 class="text-lg font-medium text-gray-900 mb-2 flex items-center">
                                        <i class="fas fa-reply text-primary mr-2"></i>
                                        Response
                                    </h5>
                                    <div class="bg-gray-50 p-4 rounded-lg">
                                        <div class="flex items-center justify-between">
                                            <div class="w-full">
                                                <pre class="text-sm font-mono text-gray-800"><span class="text-purple-600">{</span>
    <span class="text-blue-600">"success"</span>: <span class="text-orange-600">true</span>,
    <span class="text-blue-600">"message"</span>: <span class="text-green-600">"User registered successfully"</span>,
    <span class="text-blue-600">"data"</span>: <span class="text-purple-600">{</span>
        <span class="text-blue-600">"user_id"</span>: <span class="text-orange-600">123</span>,
        <span class="text-blue-600">"email"</span>: <span class="text-green-600">"user@example.com"</span>
    <span class="text-purple-600">}</span>
<span class="text-purple-600">}</span></pre>
                                            </div>
                                            <button onclick="copyToClipboard(registerResponse)" class="text-gray-500 hover:text-gray-700 transition-colors duration-200 ml-4">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Response Status -->
                                <div class="mt-4">
                                    <div class="flex items-center space-x-2">
                                        <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded">Status: 200 OK</span>
                                        <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded">Content-Type: application/json</span>
                                    </div>
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
                                <!-- Headers Section -->
                                <div>
                                    <h5 class="text-lg font-medium text-gray-900 mb-2 flex items-center">
                                        <i class="fas fa-heading text-primary mr-2"></i>
                                        Headers
                                    </h5>
                                    <div class="bg-gray-50 p-4 rounded-lg">
                                        <div class="flex items-center justify-between">
                                            <div class="space-y-2">
                                                <div class="flex items-center">
                                                    <span class="text-green-600 font-semibold mr-2">Content-Type:</span>
                                                    <code class="text-sm font-mono text-gray-800">application/json</code>
                                                </div>
                                                <div class="flex items-center">
                                                    <span class="text-green-600 font-semibold mr-2">X-API-Key:</span>
                                                    <code class="text-sm font-mono text-gray-800">your_api_key</code>
                                                </div>
                                            </div>
                                            <button onclick="copyToClipboard('Content-Type: application/json\nX-API-Key: your_api_key')" 
                                                    class="text-gray-500 hover:text-gray-700 transition-colors duration-200">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Request Body Section -->
                                <div>
                                    <h5 class="text-lg font-medium text-gray-900 mb-2 flex items-center">
                                        <i class="fas fa-code text-primary mr-2"></i>
                                        Request Body
                                    </h5>
                                    <div class="bg-gray-50 p-4 rounded-lg">
                                        <div class="flex items-center justify-between">
                                            <div class="w-full">
                                                <pre class="text-sm font-mono text-gray-800"><span class="text-purple-600">{</span>
    <span class="text-blue-600">"email"</span>: <span class="text-green-600">"user@example.com"</span>,
    <span class="text-blue-600">"purpose"</span>: <span class="text-green-600">"email-verification"</span> <span class="text-gray-500">// or "password-reset"</span>
<span class="text-purple-600">}</span></pre>
                                            </div>
                                            <button onclick="copyToClipboard(otpRequest)" class="text-gray-500 hover:text-gray-700 transition-colors duration-200 ml-4">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Response Section -->
                                <div>
                                    <h5 class="text-lg font-medium text-gray-900 mb-2 flex items-center">
                                        <i class="fas fa-reply text-primary mr-2"></i>
                                        Response
                                    </h5>
                                    <div class="bg-gray-50 p-4 rounded-lg">
                                        <div class="flex items-center justify-between">
                                            <div class="w-full">
                                                <pre class="text-sm font-mono text-gray-800"><span class="text-purple-600">{</span>
    <span class="text-blue-600">"success"</span>: <span class="text-orange-600">true</span>,
    <span class="text-blue-600">"message"</span>: <span class="text-green-600">"OTP generated successfully"</span>,
    <span class="text-blue-600">"data"</span>: <span class="text-purple-600">{</span>
        <span class="text-blue-600">"user_id"</span>: <span class="text-orange-600">123</span>,
        <span class="text-blue-600">"email"</span>: <span class="text-green-600">"user@example.com"</span>,
        <span class="text-blue-600">"purpose"</span>: <span class="text-green-600">"email-verification"</span>,
        <span class="text-blue-600">"otp"</span>: <span class="text-green-600">"123456"</span>,
        <span class="text-blue-600">"otp_expires_at"</span>: <span class="text-green-600">"2024-03-21 12:34:56"</span>,
        <span class="text-blue-600">"auth_token"</span>: <span class="text-green-600">"your_auth_token"</span>,
        <span class="text-blue-600">"token_expires_at"</span>: <span class="text-green-600">"2024-03-22 10:30:00"</span>
    <span class="text-purple-600">}</span>
<span class="text-purple-600">}</span></pre>
                                            </div>
                                            <button onclick="copyToClipboard(otpResponse)" class="text-gray-500 hover:text-gray-700 transition-colors duration-200 ml-4">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Response Status -->
                                <div class="mt-4">
                                    <div class="flex items-center space-x-2">
                                        <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded">Status: 200 OK</span>
                                        <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded">Content-Type: application/json</span>
                                    </div>
                                </div>

                                <!-- Rate Limiting Note -->
                                <div class="mt-4 bg-yellow-50 border-l-4 border-yellow-400 p-4">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm text-yellow-700">
                                                <strong>Rate Limiting:</strong> OTP requests are limited to one per minute per email address.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Verify Email -->
                        <div class="bg-white rounded-lg shadow-md p-6">
                            <div class="flex items-center mb-4">
                                <span class="bg-green-500 text-white px-3 py-1 rounded-md text-sm font-medium mr-3">POST</span>
                                <h4 class="text-xl font-semibold text-gray-900">/api/verify-email.php</h4>
                            </div>
                            <p class="text-gray-600 mb-4">Verify user's email address using OTP.</p>
                            
                            <div class="space-y-4">
                                <!-- Headers Section -->
                                <div>
                                    <h5 class="text-lg font-medium text-gray-900 mb-2 flex items-center">
                                        <i class="fas fa-heading text-primary mr-2"></i>
                                        Headers
                                    </h5>
                                    <div class="bg-gray-50 p-4 rounded-lg">
                                        <div class="flex items-center justify-between">
                                            <div class="space-y-2">
                                                <div class="flex items-center">
                                                    <span class="text-green-600 font-semibold mr-2">Content-Type:</span>
                                                    <code class="text-sm font-mono text-gray-800">application/json</code>
                                                </div>
                                                <div class="flex items-center">
                                                    <span class="text-green-600 font-semibold mr-2">X-API-Key:</span>
                                                    <code class="text-sm font-mono text-gray-800">your_api_key</code>
                                                </div>
                                                <div class="flex items-center">
                                                    <span class="text-green-600 font-semibold mr-2">Authorization:</span>
                                                    <code class="text-sm font-mono text-gray-800">Bearer auth_token</code>
                                                </div>
                                            </div>
                                            <button onclick="copyToClipboard('Content-Type: application/json\nX-API-Key: your_api_key\nAuthorization: Bearer auth_token')" 
                                                    class="text-gray-500 hover:text-gray-700 transition-colors duration-200">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Request Body Section -->
                                <div>
                                    <h5 class="text-lg font-medium text-gray-900 mb-2 flex items-center">
                                        <i class="fas fa-code text-primary mr-2"></i>
                                        Request Body
                                    </h5>
                                    <div class="bg-gray-50 p-4 rounded-lg">
                                        <div class="flex items-center justify-between">
                                            <div class="w-full">
                                                <pre class="text-sm font-mono text-gray-800"><span class="text-purple-600">{</span>
    <span class="text-blue-600">"email"</span>: <span class="text-green-600">"user@example.com"</span>,
    <span class="text-blue-600">"otp"</span>: <span class="text-green-600">"123456"</span>
<span class="text-purple-600">}</span></pre>
                                            </div>
                                            <button onclick="copyToClipboard(verifyEmailRequest)" class="text-gray-500 hover:text-gray-700 transition-colors duration-200 ml-4">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Response Section -->
                                <div>
                                    <h5 class="text-lg font-medium text-gray-900 mb-2 flex items-center">
                                        <i class="fas fa-reply text-primary mr-2"></i>
                                        Response
                                    </h5>
                                    <div class="bg-gray-50 p-4 rounded-lg">
                                        <div class="flex items-center justify-between">
                                            <div class="w-full">
                                                <pre class="text-sm font-mono text-gray-800"><span class="text-purple-600">{</span>
    <span class="text-blue-600">"success"</span>: <span class="text-orange-600">true</span>,
    <span class="text-blue-600">"message"</span>: <span class="text-green-600">"Email verified successfully"</span>,
    <span class="text-blue-600">"data"</span>: <span class="text-purple-600">{</span>
        <span class="text-blue-600">"user_id"</span>: <span class="text-orange-600">123</span>,
        <span class="text-blue-600">"email"</span>: <span class="text-green-600">"user@example.com"</span>,
        <span class="text-blue-600">"auth_token"</span>: <span class="text-green-600">"new_auth_token"</span>,
        <span class="text-blue-600">"expires_at"</span>: <span class="text-green-600">"2024-03-22 10:30:00"</span>
    <span class="text-purple-600">}</span>
<span class="text-purple-600">}</span></pre>
                                            </div>
                                            <button onclick="copyToClipboard(verifyEmailResponse)" class="text-gray-500 hover:text-gray-700 transition-colors duration-200 ml-4">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Response Status -->
                                <div class="mt-4">
                                    <div class="flex items-center space-x-2">
                                        <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded">Status: 200 OK</span>
                                        <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded">Content-Type: application/json</span>
                                    </div>
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
                                <!-- Headers Section -->
                                <div>
                                    <h5 class="text-lg font-medium text-gray-900 mb-2 flex items-center">
                                        <i class="fas fa-heading text-primary mr-2"></i>
                                        Headers
                                    </h5>
                                    <div class="bg-gray-50 p-4 rounded-lg">
                                        <div class="flex items-center justify-between">
                                            <div class="space-y-2">
                                                <div class="flex items-center">
                                                    <span class="text-green-600 font-semibold mr-2">Content-Type:</span>
                                                    <code class="text-sm font-mono text-gray-800">application/json</code>
                                                </div>
                                                <div class="flex items-center">
                                                    <span class="text-green-600 font-semibold mr-2">X-API-Key:</span>
                                                    <code class="text-sm font-mono text-gray-800">your_api_key</code>
                                                </div>
                                                <div class="flex items-center">
                                                    <span class="text-green-600 font-semibold mr-2">Authorization:</span>
                                                    <code class="text-sm font-mono text-gray-800">Bearer auth_token</code>
                                                </div>
                                            </div>
                                            <button onclick="copyToClipboard('Content-Type: application/json\nX-API-Key: your_api_key\nAuthorization: Bearer auth_token')" 
                                                    class="text-gray-500 hover:text-gray-700 transition-colors duration-200">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Request Body Section -->
                                <div>
                                    <h5 class="text-lg font-medium text-gray-900 mb-2 flex items-center">
                                        <i class="fas fa-code text-primary mr-2"></i>
                                        Request Body
                                    </h5>
                                    <div class="bg-gray-50 p-4 rounded-lg">
                                        <div class="flex items-center justify-between">
                                            <div class="w-full">
                                                <pre class="text-sm font-mono text-gray-800"><span class="text-purple-600">{</span>
    <span class="text-blue-600">"otp"</span>: <span class="text-green-600">"123456"</span>,
    <span class="text-blue-600">"new_password"</span>: <span class="text-green-600">"NewSecurePass123"</span>
<span class="text-purple-600">}</span></pre>
                                            </div>
                                            <button onclick="copyToClipboard(resetPasswordRequest)" class="text-gray-500 hover:text-gray-700 transition-colors duration-200 ml-4">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Response Section -->
                                <div>
                                    <h5 class="text-lg font-medium text-gray-900 mb-2 flex items-center">
                                        <i class="fas fa-reply text-primary mr-2"></i>
                                        Response
                                    </h5>
                                    <div class="bg-gray-50 p-4 rounded-lg">
                                        <div class="flex items-center justify-between">
                                            <div class="w-full">
                                                <pre class="text-sm font-mono text-gray-800"><span class="text-purple-600">{</span>
    <span class="text-blue-600">"success"</span>: <span class="text-orange-600">true</span>,
    <span class="text-blue-600">"message"</span>: <span class="text-green-600">"Password reset successfully"</span>,
    <span class="text-blue-600">"data"</span>: <span class="text-purple-600">{</span>
        <span class="text-blue-600">"email"</span>: <span class="text-green-600">"user@example.com"</span>,
        <span class="text-blue-600">"token_expires_at"</span>: <span class="text-green-600">"2024-03-22 10:30:00"</span>
    <span class="text-purple-600">}</span>
<span class="text-purple-600">}</span></pre>
                                            </div>
                                            <button onclick="copyToClipboard(resetPasswordResponse)" class="text-gray-500 hover:text-gray-700 transition-colors duration-200 ml-4">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Response Status -->
                                <div class="mt-4">
                                    <div class="flex items-center space-x-2">
                                        <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded">Status: 200 OK</span>
                                        <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded">Content-Type: application/json</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Refresh Token -->
                        <div class="bg-white rounded-lg shadow-md p-6">
                            <div class="flex items-center mb-4">
                                <span class="bg-green-500 text-white px-3 py-1 rounded-md text-sm font-medium mr-3">POST</span>
                                <h4 class="text-xl font-semibold text-gray-900">/api/refresh-token.php</h4>
                            </div>
                            <p class="text-gray-600 mb-4">Refresh an authentication token before it expires.</p>
                            
                            <div class="space-y-4">
                                <!-- Headers Section -->
                                <div>
                                    <h5 class="text-lg font-medium text-gray-900 mb-2 flex items-center">
                                        <i class="fas fa-heading text-primary mr-2"></i>
                                        Headers
                                    </h5>
                                    <div class="bg-gray-50 p-4 rounded-lg">
                                        <div class="flex items-center justify-between">
                                            <div class="space-y-2">
                                                <div class="flex items-center">
                                                    <span class="text-green-600 font-semibold mr-2">Content-Type:</span>
                                                    <code class="text-sm font-mono text-gray-800">application/json</code>
                                                </div>
                                                <div class="flex items-center">
                                                    <span class="text-green-600 font-semibold mr-2">X-API-Key:</span>
                                                    <code class="text-sm font-mono text-gray-800">your_api_key</code>
                                                </div>
                                                <div class="flex items-center">
                                                    <span class="text-green-600 font-semibold mr-2">Authorization:</span>
                                                    <code class="text-sm font-mono text-gray-800">Bearer current_auth_token</code>
                                                </div>
                                            </div>
                                            <button onclick="copyToClipboard('Content-Type: application/json\nX-API-Key: your_api_key\nAuthorization: Bearer current_auth_token')" 
                                                    class="text-gray-500 hover:text-gray-700 transition-colors duration-200">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Response Section -->
                                <div>
                                    <h5 class="text-lg font-medium text-gray-900 mb-2 flex items-center">
                                        <i class="fas fa-reply text-primary mr-2"></i>
                                        Response
                                    </h5>
                                    <div class="bg-gray-50 p-4 rounded-lg">
                                        <div class="flex items-center justify-between">
                                            <div class="w-full">
                                                <pre class="text-sm font-mono text-gray-800"><span class="text-purple-600">{</span>
    <span class="text-blue-600">"success"</span>: <span class="text-orange-600">true</span>,
    <span class="text-blue-600">"message"</span>: <span class="text-green-600">"Token refreshed successfully"</span>,
    <span class="text-blue-600">"data"</span>: <span class="text-purple-600">{</span>
        <span class="text-blue-600">"token"</span>: <span class="text-green-600">"new_auth_token"</span>,
        <span class="text-blue-600">"expires_at"</span>: <span class="text-green-600">"2024-03-22 10:30:00"</span>
    <span class="text-purple-600">}</span>
<span class="text-purple-600">}</span></pre>
                                            </div>
                                            <button onclick="copyToClipboard(refreshTokenResponse)" class="text-gray-500 hover:text-gray-700 transition-colors duration-200 ml-4">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Response Status -->
                                <div class="mt-4">
                                    <div class="flex items-center space-x-2">
                                        <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded">Status: 200 OK</span>
                                        <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded">Content-Type: application/json</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Delete User -->
                        <div class="bg-white rounded-lg shadow-md p-6">
                            <div class="flex items-center mb-4">
                                <span class="bg-red-500 text-white px-3 py-1 rounded-md text-sm font-medium mr-3">POST</span>
                                <h4 class="text-xl font-semibold text-gray-900">/api/delete-user.php</h4>
                            </div>
                            <p class="text-gray-600 mb-4">Delete a user account and all associated data. This endpoint requires both authentication token and email verification for enhanced security.</p>
                            
                            <div class="space-y-4">
                                <!-- Headers Section -->
                                <div>
                                    <h5 class="text-lg font-medium text-gray-900 mb-2 flex items-center">
                                        <i class="fas fa-heading text-primary mr-2"></i>
                                        Headers
                                    </h5>
                                    <div class="bg-gray-50 p-4 rounded-lg">
                                        <div class="flex items-center justify-between">
                                            <div class="space-y-2">
                                                <div class="flex items-center">
                                                    <span class="text-green-600 font-semibold mr-2">Content-Type:</span>
                                                    <code class="text-sm font-mono text-gray-800">application/json</code>
                                                </div>
                                                <div class="flex items-center">
                                                    <span class="text-green-600 font-semibold mr-2">X-API-Key:</span>
                                                    <code class="text-sm font-mono text-gray-800">your_api_key</code>
                                                </div>
                                                <div class="flex items-center">
                                                    <span class="text-green-600 font-semibold mr-2">Authorization:</span>
                                                    <code class="text-sm font-mono text-gray-800">Bearer auth_token</code>
                                                </div>
                                            </div>
                                            <button onclick="copyToClipboard('Content-Type: application/json\nX-API-Key: your_api_key\nAuthorization: Bearer auth_token')" 
                                                    class="text-gray-500 hover:text-gray-700 transition-colors duration-200">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Request Body Section -->
                                <div>
                                    <h5 class="text-lg font-medium text-gray-900 mb-2 flex items-center">
                                        <i class="fas fa-code text-primary mr-2"></i>
                                        Request Body
                                    </h5>
                                    <div class="bg-gray-50 p-4 rounded-lg">
                                        <div class="flex items-center justify-between">
                                            <div class="w-full">
                                                <pre class="text-sm font-mono text-gray-800"><span class="text-purple-600">{</span>
    <span class="text-blue-600">"email"</span>: <span class="text-green-600">"user@example.com"</span>
<span class="text-purple-600">}</span></pre>
                                            </div>
                                            <button onclick="copyToClipboard(deleteUserRequest)" class="text-gray-500 hover:text-gray-700 transition-colors duration-200 ml-4">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Response Section -->
                                <div>
                                    <h5 class="text-lg font-medium text-gray-900 mb-2 flex items-center">
                                        <i class="fas fa-reply text-primary mr-2"></i>
                                        Response
                                    </h5>
                                    <div class="bg-gray-50 p-4 rounded-lg">
                                        <div class="flex items-center justify-between">
                                            <div class="w-full">
                                                <pre class="text-sm font-mono text-gray-800"><span class="text-purple-600">{</span>
    <span class="text-blue-600">"success"</span>: <span class="text-orange-600">true</span>,
    <span class="text-blue-600">"message"</span>: <span class="text-green-600">"User account deleted successfully"</span>,
    <span class="text-blue-600">"data"</span>: <span class="text-purple-600">{</span>
        <span class="text-blue-600">"email"</span>: <span class="text-green-600">"user@example.com"</span>
    <span class="text-purple-600">}</span>
<span class="text-purple-600">}</span></pre>
                                            </div>
                                            <button onclick="copyToClipboard(deleteUserResponse)" class="text-gray-500 hover:text-gray-700 transition-colors duration-200 ml-4">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Response Status -->
                                <div class="mt-4">
                                    <div class="flex items-center space-x-2">
                                        <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded">Status: 200 OK</span>
                                        <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded">Content-Type: application/json</span>
                                    </div>
                                </div>

                                <!-- Error Responses -->
                                <div>
                                    <h5 class="text-lg font-medium text-gray-900 mb-2 flex items-center">
                                        <i class="fas fa-exclamation-circle text-red-500 mr-2"></i>
                                        Error Responses
                                    </h5>
                                    <div class="space-y-4">
                                        <div class="bg-gray-50 p-4 rounded-lg">
                                            <div class="flex items-center justify-between">
                                                <div class="w-full">
                                                    <pre class="text-sm font-mono text-gray-800"><span class="text-purple-600">{</span>
    <span class="text-blue-600">"success"</span>: <span class="text-orange-600">false</span>,
    <span class="text-blue-600">"error"</span>: <span class="text-green-600">"Invalid request"</span>,
    <span class="text-blue-600">"message"</span>: <span class="text-green-600">"No matching user found with the provided email and token"</span>
<span class="text-purple-600">}</span></pre>
                                                </div>
                                                <button onclick="copyToClipboard(invalidRequestError)" class="text-gray-500 hover:text-gray-700 transition-colors duration-200 ml-4">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <div class="bg-gray-50 p-4 rounded-lg">
                                            <div class="flex items-center justify-between">
                                                <div class="w-full">
                                                    <pre class="text-sm font-mono text-gray-800"><span class="text-purple-600">{</span>
    <span class="text-blue-600">"success"</span>: <span class="text-orange-600">false</span>,
    <span class="text-blue-600">"error"</span>: <span class="text-green-600">"Token expired"</span>,
    <span class="text-blue-600">"message"</span>: <span class="text-green-600">"The authentication token has expired"</span>,
    <span class="text-blue-600">"expired_at"</span>: <span class="text-green-600">"2024-03-21 12:34:56"</span>
<span class="text-purple-600">}</span></pre>
                                                </div>
                                                <button onclick="copyToClipboard(tokenExpiredError)" class="text-gray-500 hover:text-gray-700 transition-colors duration-200 ml-4">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Security Notes -->
                                <div class="mt-4 bg-yellow-50 border-l-4 border-yellow-400 p-4">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm text-yellow-700">
                                                <strong>Security Notes:</strong>
                                                <ul class="list-disc pl-6">
                                                    <li>Requires valid API key in X-API-Key header</li>
                                                    <li>Requires valid auth token in Authorization header</li>
                                                    <li>Email must match the user associated with the auth token</li>
                                                    <li>Uses database transactions to ensure data consistency</li>
                                                    <li>Deletes all associated data:
                                                        <ul class="list-disc pl-6">
                                                            <li>Authentication tokens</li>
                                                            <li>Email verification OTPs</li>
                                                            <li>Password reset OTPs</li>
                                                            <li>User account data</li>
                                                        </ul>
                                                    </li>
                                                </ul>
                                                <strong>Warning:</strong> This action is irreversible. All user data will be permanently deleted.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Error Responses Section -->
                    <div id="error-responses" class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center mb-4">
                            <i class="fas fa-exclamation-circle text-red-500 text-2xl mr-3"></i>
                            <h3 class="text-2xl font-semibold text-gray-900">Error Responses</h3>
                        </div>
                        <p class="text-gray-600 mb-4">All endpoints return consistent error responses in the following format:</p>
                        <div class="bg-gray-50 p-4 rounded-lg mb-6">
                            <div class="flex items-center justify-between">
                                <code class="text-sm font-mono text-gray-800">{
    "success": false,
    "error": "error_type",
    "message": "Human-readable error message"
}</code>
                                <button onclick="copyToClipboard(errorResponse)" class="text-gray-500 hover:text-gray-700">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>

                        <h4 class="text-lg font-medium text-gray-900 mb-4">Common Error Types</h4>
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
                                    <!-- Add other error types... -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Integration Guide Section -->
                    <div id="integration" class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center mb-4">
                            <i class="fas fa-book text-primary text-2xl mr-3"></i>
                            <h3 class="text-2xl font-semibold text-gray-900">Getting Started</h3>
                        </div>
                        
                        <!-- Setup Requirements -->
                        <div class="mb-8">
                            <h4 class="text-lg font-medium text-gray-900 mb-4">Setup Requirements</h4>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <ul class="list-disc pl-6 space-y-2 text-gray-600">
                                    <li>PHP 7.4 or higher</li>
                                    <li>MySQL 5.7 or higher</li>
                                    <li>Apache/Nginx web server</li>
                                    <li>SSL certificate (recommended for production)</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Installation Steps -->
                        <div class="mb-8">
                            <h4 class="text-lg font-medium text-gray-900 mb-4">Installation Steps</h4>
                            <div class="space-y-4">
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h5 class="font-medium text-gray-900 mb-2">1. Database Setup</h5>
                                    <p class="text-gray-600 mb-2">Create a new MySQL database and import the provided SQL schema:</p>
                                    <div class="bg-gray-100 p-3 rounded-md">
                                        <code class="text-sm font-mono text-gray-800">mysql -u your_username -p your_database < schema.sql</code>
                                    </div>
                                </div>

                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h5 class="font-medium text-gray-900 mb-2">2. Configuration</h5>
                                    <p class="text-gray-600 mb-2">Update the database configuration in <code class="text-sm font-mono bg-gray-100 px-1 py-0.5 rounded">config/database.php</code>:</p>
                                    <div class="bg-gray-100 p-3 rounded-md">
                                        <pre class="text-sm font-mono text-gray-800">define('DB_HOST', 'localhost');
define('DB_NAME', 'your_database');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');</pre>
                                    </div>
                                </div>

                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h5 class="font-medium text-gray-900 mb-2">3. API Key Setup</h5>
                                    <p class="text-gray-600 mb-2">Register as a developer to get your API key:</p>
                                    <ol class="list-decimal pl-6 space-y-2 text-gray-600">
                                        <li>Visit the registration page</li>
                                        <li>Fill in your details and system information</li>
                                        <li>Receive your API key</li>
                                        <li>Store the API key securely</li>
                                    </ol>
                                </div>
                            </div>
                        </div>

                        <!-- API Usage -->
                        <div class="mb-8">
                            <h4 class="text-lg font-medium text-gray-900 mb-4">Using the API</h4>
                            <div class="space-y-4">
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h5 class="font-medium text-gray-900 mb-2">Authentication</h5>
                                    <p class="text-gray-600 mb-2">Include your API key in all requests:</p>
                                    <div class="bg-gray-100 p-3 rounded-md">
                                        <code class="text-sm font-mono text-gray-800">X-API-Key: your_api_key</code>
                                    </div>
                                </div>

                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h5 class="font-medium text-gray-900 mb-2">Available Endpoints</h5>
                                    <div class="space-y-2">
                                        <div class="flex items-center">
                                            <span class="bg-green-500 text-white px-2 py-1 rounded text-xs font-medium mr-2">POST</span>
                                            <code class="text-sm font-mono text-gray-800">/api/register.php</code>
                                            <span class="ml-2 text-gray-600">Register new user</span>
                                        </div>
                                        <div class="flex items-center">
                                            <span class="bg-green-500 text-white px-2 py-1 rounded text-xs font-medium mr-2">POST</span>
                                            <code class="text-sm font-mono text-gray-800">/api/login.php</code>
                                            <span class="ml-2 text-gray-600">User login</span>
                                        </div>
                                        <div class="flex items-center">
                                            <span class="bg-green-500 text-white px-2 py-1 rounded text-xs font-medium mr-2">POST</span>
                                            <code class="text-sm font-mono text-gray-800">/api/request-otp.php</code>
                                            <span class="ml-2 text-gray-600">Request OTP for verification</span>
                                        </div>
                                        <div class="flex items-center">
                                            <span class="bg-green-500 text-white px-2 py-1 rounded text-xs font-medium mr-2">POST</span>
                                            <code class="text-sm font-mono text-gray-800">/api/verify-email.php</code>
                                            <span class="ml-2 text-gray-600">Verify email with OTP</span>
                                        </div>
                                        <div class="flex items-center">
                                            <span class="bg-green-500 text-white px-2 py-1 rounded text-xs font-medium mr-2">POST</span>
                                            <code class="text-sm font-mono text-gray-800">/api/reset-password.php</code>
                                            <span class="ml-2 text-gray-600">Reset user password</span>
                                        </div>
                                        <div class="flex items-center">
                                            <span class="bg-green-500 text-white px-2 py-1 rounded text-xs font-medium mr-2">POST</span>
                                            <code class="text-sm font-mono text-gray-800">/api/refresh-token.php</code>
                                            <span class="ml-2 text-gray-600">Refresh authentication token</span>
                                        </div>
                                        <div class="flex items-center">
                                            <span class="bg-red-500 text-white px-2 py-1 rounded text-xs font-medium mr-2">POST</span>
                                            <code class="text-sm font-mono text-gray-800">/api/delete_user.php</code>
                                            <span class="ml-2 text-gray-600">Delete user account</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h5 class="font-medium text-gray-900 mb-2">Security Best Practices</h5>
                                    <ul class="list-disc pl-6 space-y-2 text-gray-600">
                                        <li>Always use HTTPS in production</li>
                                        <li>Store API keys securely</li>
                                        <li>Implement rate limiting</li>
                                        <li>Validate all input data</li>
                                        <li>Use secure password hashing</li>
                                        <li>Implement proper error handling</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Example Implementation -->
                        <div>
                            <h4 class="text-lg font-medium text-gray-900 mb-4">Example Implementation</h4>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="text-gray-600 mb-4">Here's how to make API requests using Postman:</p>
                                
                                <!-- Postman Setup -->
                                <div class="space-y-6">
                                    <div>
                                        <h5 class="font-medium text-gray-900 mb-2">1. Create a New Request</h5>
                                        <div class="bg-gray-100 p-3 rounded-md">
                                            <ol class="list-decimal pl-6 space-y-2 text-gray-600">
                                                <li>Open Postman and click "New" → "Request"</li>
                                                <li>Name your request (e.g., "Register User")</li>
                                                <li>Select "POST" as the request method</li>
                                                <li>Enter your API endpoint URL: <code class="text-sm font-mono bg-gray-200 px-1 py-0.5 rounded">https://your-domain.com/api/register.php</code></li>
                                            </ol>
                                        </div>
                                    </div>

                                    <div>
                                        <h5 class="font-medium text-gray-900 mb-2">2. Set Headers</h5>
                                        <div class="bg-gray-100 p-3 rounded-md">
                                            <p class="text-gray-600 mb-2">Go to the "Headers" tab and add:</p>
                                            <div class="space-y-2">
                                                <div class="flex items-center">
                                                    <span class="w-32 text-sm font-mono bg-gray-200 px-2 py-1 rounded">Content-Type</span>
                                                    <span class="mx-2">→</span>
                                                    <span class="text-sm font-mono bg-gray-200 px-2 py-1 rounded">application/json</span>
                                                </div>
                                                <div class="flex items-center">
                                                    <span class="w-32 text-sm font-mono bg-gray-200 px-2 py-1 rounded">X-API-Key</span>
                                                    <span class="mx-2">→</span>
                                                    <span class="text-sm font-mono bg-gray-200 px-2 py-1 rounded">your_api_key</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <h5 class="font-medium text-gray-900 mb-2">3. Set Request Body</h5>
                                        <div class="bg-gray-100 p-3 rounded-md">
                                            <ol class="list-decimal pl-6 space-y-2 text-gray-600">
                                                <li>Go to the "Body" tab</li>
                                                <li>Select "raw" and choose "JSON" from the dropdown</li>
                                                <li>Enter your request body:</li>
                                            </ol>
                                            <div class="mt-2 bg-gray-200 p-3 rounded-md">
                                                <pre class="text-sm font-mono text-gray-800">{
    "email": "user@example.com",
    "password": "SecurePass123",
    "name": "John Doe"
}</pre>
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <h5 class="font-medium text-gray-900 mb-2">4. Send Request</h5>
                                        <div class="bg-gray-100 p-3 rounded-md">
                                            <ol class="list-decimal pl-6 space-y-2 text-gray-600">
                                                <li>Click the "Send" button</li>
                                                <li>View the response in the lower panel</li>
                                                <li>Check the status code and response body</li>
                                            </ol>
                                        </div>
                                    </div>

                                    <div>
                                        <h5 class="font-medium text-gray-900 mb-2">Example Response</h5>
                                        <div class="bg-gray-100 p-3 rounded-md">
                                            <pre class="text-sm font-mono text-gray-800">{
    "success": true,
    "message": "User registered successfully",
    "data": {
        "user_id": 123,
        "email": "user@example.com"
    }
}</pre>
                                        </div>
                                    </div>

                                    <div class="bg-blue-50 border-l-4 border-blue-400 p-4">
                                        <div class="flex">
                                            <div class="flex-shrink-0">
                                                <i class="fas fa-info-circle text-blue-400"></i>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm text-blue-700">
                                                    <strong>Tip:</strong> You can save your API key as a Postman environment variable to reuse it across requests. Go to "Environments" → "Create New" and add your API key as a variable.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Security Best Practices Section -->
                    <div id="security" class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center mb-4">
                            <i class="fas fa-shield-alt text-primary text-2xl mr-3"></i>
                            <h3 class="text-2xl font-semibold text-gray-900">Security Best Practices</h3>
                        </div>
                        <!-- Security content... -->
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