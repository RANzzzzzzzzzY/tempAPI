<?php
// include 'documentation.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Developer Dashboard - User Auth API</title>
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
<body class="bg-gray-50 min-h-screen">
    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="fixed inset-0 bg-white/80 flex items-center justify-center z-50 hidden">
        <i class="fas fa-circle-notch fa-spin fa-3x text-secondary"></i>
    </div>

    <div class="flex">
        <!-- Sidebar -->
        <div class="w-64 bg-primary shadow-sm h-screen sticky top-0 overflow-y-auto">
            <div class="p-4 border-b border-white/10 sticky top-0 bg-primary z-10">
                <a href="#" class="flex items-center">
                    <i class="fas fa-shield-alt text-white text-2xl mr-2"></i>
                    <span class="text-xl font-bold text-white">User Auth API</span>
                </a>
            </div>
            <div class="p-4 space-y-1">
                <a href="#overview" data-section="overview" class="nav-link flex items-center px-4 py-2 text-sm font-medium text-white rounded-md hover:bg-white/10 active">
                    <i class="fas fa-home w-5"></i>
                    <span>Overview</span>
                </a>
                <a href="#api-keys" data-section="api-keys" class="nav-link flex items-center px-4 py-2 text-sm font-medium text-white/80 rounded-md hover:bg-white/10">
                    <i class="fas fa-key w-5"></i>
                    <span>API Keys</span>
                </a>
                <a href="#documentation" data-section="documentation" class="nav-link flex items-center px-4 py-2 text-sm font-medium text-white/80 rounded-md hover:bg-white/10">
                    <i class="fas fa-book w-5"></i>
                    <span>Documentation</span>
                </a>
                <a href="#support" class="flex items-center px-4 py-2 text-sm font-medium text-white/80 rounded-md hover:bg-white/10">
                    <i class="fas fa-question-circle w-5"></i>
                    <span>Support</span>
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 bg-gray-50">
            <!-- Top Bar with User Menu -->
            <div class="bg-white border-b border-gray-200">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-end h-16">
                        <div class="flex items-center">
                            <div class="relative" x-data="{ open: false }">
                                <button onclick="toggleDropdown()" class="flex items-center px-3 py-2 text-gray-700 hover:text-secondary focus:outline-none rounded-md hover:bg-gray-50 transition-colors duration-200">
                                    <i class="fas fa-user-circle text-lg mr-2"></i>
                                    <span id="userEmail" class="text-sm font-medium">Loading...</span>
                                    <i class="fas fa-chevron-down ml-2 text-xs transition-transform duration-200"></i>
                                </button>
                                <div id="userDropdown" class="hidden absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg py-2 z-10 border border-gray-100">
                                    <div class="px-4 py-2 border-b border-gray-100">
                                        <p class="text-sm font-medium text-gray-900">Signed in as</p>
                                        <p id="userEmailDropdown" class="text-sm text-gray-500 truncate">Loading...</p>
                                    </div>
                                    <a href="../index.php" id="logoutBtn" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-secondary transition-colors duration-200">
                                        <i class="fas fa-sign-out-alt w-5"></i>
                                        <span>Sign out</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <!-- Overview Section -->
                <section id="overview" class="content-section">
                    <div class="flex items-center justify-between mb-8">
                        <div>
                            <h2 class="text-2xl font-bold text-primary">Dashboard Overview</h2>
                            <p class="text-sm text-gray-500 mt-1">Welcome back! Here's what's happening with your API.</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="text-sm text-gray-500">Last updated:</span>
                            <span class="text-sm font-medium text-gray-700" id="lastUpdated">Just now</span>
                        </div>
                    </div>
                    
                    <!-- Email Verification Alert -->
                    <div id="verificationAlert" class="hidden mb-8 bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-r-md">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700">
                                    Your email is not verified. Some features may be limited.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="bg-white rounded-lg shadow-lg p-6 hover:shadow-md transition-shadow duration-200">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-medium text-gray-900">API Calls</h3>
                                <div class="p-2 bg-blue-50 rounded-lg">
                                    <i class="fas fa-chart-line text-secondary"></i>
                                </div>
                            </div>
                            <div class="text-3xl font-bold text-secondary">0</div>
                            <p class="text-sm text-gray-500 mt-1">Last 30 days</p>
                            <div class="mt-4 pt-4 border-t border-gray-100">
                                <div class="flex items-center text-sm text-gray-500">
                                    <i class="fas fa-arrow-up text-green-500 mr-1"></i>
                                    <span>0% from last month</span>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white rounded-lg shadow-lg p-6 hover:shadow-md transition-shadow duration-200">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-medium text-gray-900">Active Users</h3>
                                <div class="p-2 bg-blue-50 rounded-lg">
                                    <i class="fas fa-users text-secondary"></i>
                                </div>
                            </div>
                            <div class="text-3xl font-bold text-secondary">0</div>
                            <p class="text-sm text-gray-500 mt-1">Total registered users</p>
                            <div class="mt-4 pt-4 border-t border-gray-100">
                                <div class="flex items-center text-sm text-gray-500">
                                    <i class="fas fa-arrow-up text-green-500 mr-1"></i>
                                    <span>0% from last month</span>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white rounded-lg shadow-lg p-6 hover:shadow-md transition-shadow duration-200">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-medium text-gray-900">Success Rate</h3>
                                <div class="p-2 bg-blue-50 rounded-lg">
                                    <i class="fas fa-check-circle text-secondary"></i>
                                </div>
                            </div>
                            <div class="text-3xl font-bold text-secondary">100%</div>
                            <p class="text-sm text-gray-500 mt-1">API request success rate</p>
                            <div class="mt-4 pt-4 border-t border-gray-100">
                                <div class="flex items-center text-sm text-gray-500">
                                    <i class="fas fa-arrow-up text-green-500 mr-1"></i>
                                    <span>0% from last month</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- API Keys Section -->
                <section id="api-keys" class="content-section hidden">
                    <div class="flex items-center justify-between mb-8">
                        <div>
                            <h2 class="text-2xl font-bold text-primary">API Keys</h2>
                            <p class="text-sm text-gray-500 mt-1">Manage your API keys and access tokens.</p>
                        </div>
                        <button onclick="regenerateApiKey()" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-secondary hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-secondary transition-colors duration-200">
                            <i class="fas fa-sync-alt mr-2"></i>
                            Regenerate Key
                        </button>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <div class="mb-4">
                            <h5 class="text-lg font-medium text-gray-900 mb-2">Your API Key</h5>
                            <p id="systemName" class="text-sm text-gray-600 mb-4"></p>
                            <div class="relative">
                                <div id="apiKeyDisplay" class="bg-gray-50 rounded-md p-3 font-mono text-sm break-all pr-12 border border-gray-200">
                                    Loading...
                                </div>
                                <button onclick="copyApiKey()" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-secondary transition-colors duration-200">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                            <div class="mt-4 space-y-2">
                                <p class="text-sm text-gray-500">Keep this key secure and never share it publicly.</p>
                                <div class="flex items-center text-sm text-gray-500">
                                    <i class="fas fa-info-circle mr-2 text-secondary"></i>
                                    <span>This key is used to authenticate your API requests</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Documentation Section -->
                <section id="documentation" class="content-section hidden">
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
                            <h3 class="text-2xl font-semibold text-gray-900">Integration Guide</h3>
                        </div>
                        <!-- Integration content... -->
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

    <script>
        // Check authentication
        const userData = JSON.parse(localStorage.getItem('dev_user'));
        if (!userData) {
            window.location.href = 'login-page.php';
        }

        // Update UI with user data
        document.getElementById('userEmail').textContent = userData.email;
        document.getElementById('userEmailDropdown').textContent = userData.email;
        document.getElementById('apiKeyDisplay').textContent = userData.api_key;
        document.getElementById('systemName').innerHTML = `<strong>System Name:</strong> ${userData.system_name}`;

        // Show verification alert if needed
        if (!userData.is_email_verified) {
            document.getElementById('verificationAlert').classList.remove('hidden');
        }

        // Update last updated time
        function updateLastUpdated() {
            const now = new Date();
            const timeString = now.toLocaleTimeString();
            document.getElementById('lastUpdated').textContent = timeString;
        }
        updateLastUpdated();
        setInterval(updateLastUpdated, 60000); // Update every minute

        // Navigation
        document.querySelectorAll('.nav-link[data-section]').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const section = e.target.closest('.nav-link').dataset.section;
                
                // Update active state
                document.querySelectorAll('.nav-link').forEach(l => {
                    l.classList.remove('active', 'bg-white/10');
                    l.classList.add('text-white/80');
                });
                e.target.closest('.nav-link').classList.add('active', 'bg-white/10');
                e.target.closest('.nav-link').classList.remove('text-white/80');
                e.target.closest('.nav-link').classList.add('text-white');
                
                // Show selected section
                document.querySelectorAll('.content-section').forEach(s => s.classList.add('hidden'));
                document.getElementById(section).classList.remove('hidden');
            });
        });

        // Dropdown toggle with animation
        function toggleDropdown() {
            const dropdown = document.getElementById('userDropdown');
            const chevron = document.querySelector('button[onclick="toggleDropdown()"] .fa-chevron-down');
            
            dropdown.classList.toggle('hidden');
            chevron.style.transform = dropdown.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(180deg)';
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            const dropdown = document.getElementById('userDropdown');
            const userButton = e.target.closest('button');
            if (!userButton && !dropdown.contains(e.target)) {
                dropdown.classList.add('hidden');
                const chevron = document.querySelector('button[onclick="toggleDropdown()"] .fa-chevron-down');
                chevron.style.transform = 'rotate(0deg)';
            }
        });

        // Copy API Key
        function copyApiKey() {
            const apiKey = userData.api_key;
            navigator.clipboard.writeText(apiKey)
                .then(() => {
                    const button = document.querySelector('#apiKeyDisplay + button');
                    const icon = button.querySelector('i');
                    icon.className = 'fas fa-check';
                    setTimeout(() => {
                        icon.className = 'fas fa-copy';
                    }, 2000);
                })
                .catch(err => console.error('Failed to copy API key:', err));
        }

        // Regenerate API Key
        async function regenerateApiKey() {
            if (!confirm('Are you sure you want to regenerate your API key? The old key will stop working immediately.')) {
                return;
            }

            document.getElementById('loadingOverlay').classList.remove('hidden');

            try {
                const response = await fetch('regenerate-key.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    }
                });

                const data = await response.json();

                if (data.success) {
                    userData.api_key = data.data.api_key;
                    localStorage.setItem('dev_user', JSON.stringify(userData));
                    document.getElementById('apiKeyDisplay').textContent = data.data.api_key;
                    alert('API key regenerated successfully!');
                } else {
                    alert(data.error || 'Failed to regenerate API key');
                }
            } catch (error) {
                alert('Network error. Please try again.');
            } finally {
                document.getElementById('loadingOverlay').classList.add('hidden');
            }
        }

        // Logout
        document.getElementById('logoutBtn').addEventListener('click', (e) => {
            e.preventDefault();
            localStorage.removeItem('dev_user');
            window.location.href = '../index.php';
        });
    </script>
</body>
</html> 