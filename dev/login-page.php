<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Developer Login - User Auth API</title>
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
<body class="bg-gradient-to-r from-primary to-secondary min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-sm">
        <div class="bg-white rounded-lg shadow-xl p-6">
            <!-- Logo Section -->
            <div class="text-center mb-6">
                <h1 class="text-2xl font-bold text-primary">Developer Login</h1>
                <p class="text-gray-600 mt-1 text-sm">Access your API dashboard</p>
            </div>

            <!-- Alert Messages -->
            <div id="errorAlert" class="hidden mb-4 bg-red-50 border-l-4 border-red-400 p-3">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-circle text-red-400"></i>
                    </div>
                    <div class="ml-3">
                        <p id="errorMessage" class="text-sm text-red-700"></p>
                    </div>
                    <div class="ml-auto pl-3">
                        <button onclick="hideError()" class="text-red-400 hover:text-red-500">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div id="successAlert" class="hidden mb-4 bg-green-50 border-l-4 border-green-400 p-3">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-green-400"></i>
                    </div>
                    <div class="ml-3">
                        <p id="successMessage" class="text-sm text-green-700"></p>
                    </div>
                </div>
            </div>

            <!-- Login Form -->
            <form id="loginForm" class="space-y-4" novalidate>
                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
                    <div class="mt-1">
                        <input type="email" id="email" name="email" required
                               class="appearance-none block w-full px-3 py-1.5 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-secondary focus:border-secondary text-sm"
                               oninput="validateEmail(this)">
                        <p id="emailError" class="mt-1 text-xs text-red-600 hidden">Please enter a valid email address.</p>
                    </div>
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <div class="mt-1 relative">
                        <input type="password" id="password" name="password" required
                               class="appearance-none block w-full px-3 py-1.5 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-secondary focus:border-secondary text-sm"
                               oninput="validatePassword(this)">
                        <button type="button" onclick="togglePassword()"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-500">
                            <i class="fas fa-eye text-sm"></i>
                        </button>
                    </div>
                    <p id="passwordError" class="mt-1 text-xs text-red-600 hidden">Password is required.</p>
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button type="submit" id="submitBtn"
                            class="w-full flex justify-center py-1.5 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-secondary focus:outline-none focus:ring-1 focus:ring-offset-1 focus:ring-secondary disabled:opacity-50 disabled:cursor-not-allowed">
                        <span id="submitText">Login</span>
                        <span id="submitSpinner" class="hidden">
                            <i class="fas fa-circle-notch fa-spin ml-2"></i>
                        </span>
                    </button>
                </div>
            </form>

            <!-- Loading Indicator -->
            <div id="loadingIndicator" class="hidden mt-4 text-center">
                <i class="fas fa-circle-notch fa-spin fa-lg text-secondary"></i>
            </div>

            <!-- Links -->
            <div class="mt-4 text-center space-y-1">
                <p class="text-xs text-gray-600">
                    Don't have an account? 
                    <a href="register-page.php" class="font-medium text-secondary hover:text-blue-600">Register here</a>
                </p>
                <p class="text-xs text-gray-600">
                    <a href="../index.php" class="font-medium text-secondary hover:text-blue-600">Back to Documentation</a>
                </p>
            </div>
        </div>
    </div>

    <script>
        // Form elements
        const form = document.getElementById('loginForm');
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');
        const submitBtn = document.getElementById('submitBtn');
        const submitText = document.getElementById('submitText');
        const submitSpinner = document.getElementById('submitSpinner');

        // Validation functions
        function validateEmail(input) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            const isValid = emailRegex.test(input.value);
            
            input.classList.toggle('border-red-300', !isValid && input.value);
            const errorElement = document.getElementById('emailError');
            errorElement.textContent = input.value ? (isValid ? '' : 'Please enter a valid email address') : 'Email is required';
            errorElement.classList.toggle('hidden', isValid);
            
            updateSubmitButton();
            return isValid;
        }

        function validatePassword(input) {
            const isValid = input.value.length > 0;
            input.classList.toggle('border-red-300', !isValid);
            const errorElement = document.getElementById('passwordError');
            errorElement.textContent = input.value ? '' : 'Password is required';
            errorElement.classList.toggle('hidden', isValid);
            updateSubmitButton();
            return isValid;
        }

        function updateSubmitButton() {
            const isValid = !emailInput.classList.contains('border-red-300') &&
                          !passwordInput.classList.contains('border-red-300') &&
                          emailInput.value && passwordInput.value;
            submitBtn.disabled = !isValid;
        }

        function togglePassword() {
            const type = passwordInput.type === 'password' ? 'text' : 'password';
            passwordInput.type = type;
            const icon = type === 'password' ? 'fa-eye' : 'fa-eye-slash';
            passwordInput.nextElementSibling.querySelector('i').className = `fas ${icon} text-sm`;
        }

        function showError(message, details = '') {
            const errorAlert = document.getElementById('errorAlert');
            const errorMessage = document.getElementById('errorMessage');
            
            errorMessage.innerHTML = message;
            if (details) {
                errorMessage.innerHTML += `<br><small class="text-red-500">${details}</small>`;
            }
            
            errorAlert.classList.remove('hidden');
            hideLoading();
        }

        function hideError() {
            document.getElementById('errorAlert').classList.add('hidden');
        }

        function showSuccess(message) {
            const successAlert = document.getElementById('successAlert');
            document.getElementById('successMessage').textContent = message;
            successAlert.classList.remove('hidden');
        }

        function showLoading() {
            submitText.classList.add('hidden');
            submitSpinner.classList.remove('hidden');
            submitBtn.disabled = true;
        }

        function hideLoading() {
            submitText.classList.remove('hidden');
            submitSpinner.classList.add('hidden');
            updateSubmitButton();
        }

        // Check if user is already logged in
        if (localStorage.getItem('dev_user')) {
            window.location.href = 'dashboard-page.php';
        }

        // Form submission
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            hideError();
            
            // Validate form
            if (!validateEmail(emailInput) || !validatePassword(passwordInput)) {
                return;
            }

            showLoading();

            try {
                const response = await fetch('login.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        email: emailInput.value.trim(),
                        password: passwordInput.value
                    })
                });

                const data = await response.json();

                if (data.success) {
                    showSuccess(data.message || 'Login successful! Redirecting...');

                    // Store user data
                    localStorage.setItem('dev_user', JSON.stringify({
                        id: data.data.id,
                        email: data.data.email,
                        full_name: data.data.full_name,
                        is_email_verified: data.data.is_email_verified,
                        api_key: data.data.api_key,
                        system_name: data.data.system_name
                    }));

                    // Redirect to dashboard
                    setTimeout(() => {
                        window.location.href = 'dashboard-page.php';
                    }, 1000);
                } else {
                    showError(
                        data.error || 'Login failed',
                        data.debug_info ? `Debug info: ${JSON.stringify(data.debug_info)}` : ''
                    );
                }
            } catch (error) {
                showError(
                    'Network error occurred',
                    'Please check your internet connection and try again.'
                );
                console.error('Login error:', error);
            } finally {
                hideLoading();
            }
        });

        // Initialize form validation
        emailInput.addEventListener('input', () => validateEmail(emailInput));
        passwordInput.addEventListener('input', () => validatePassword(passwordInput));
    </script>
</body>
</html> 