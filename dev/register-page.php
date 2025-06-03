<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Developer Registration - User Auth API</title>
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
<body class="bg-gradient-to-r from-primary to-secondary min-h-screen flex items-center justify-center p-4 font-['Poppins']">
    <div class="w-full max-w-lg">
        <div class="bg-white rounded-lg shadow-xl p-6">
            <!-- Logo Section -->
            <div class="text-center mb-6">
                <h1 class="text-2xl font-bold text-primary">Developer Registration</h1>
                <p class="text-gray-600 mt-1 text-sm">Create your API account</p>
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

            <!-- Registration Form -->
            <form id="registerForm" class="space-y-4" novalidate>
                <!-- Full Name -->
                <div>
                    <label for="fullName" class="block text-sm font-medium text-gray-700">Full Name</label>
                    <div class="mt-1">
                        <input type="text" id="fullName" name="fullName" required
                               class="appearance-none block w-full px-3 py-1.5 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-secondary focus:border-secondary text-sm"
                               oninput="validateFullName(this)">
                        <p id="fullNameError" class="mt-1 text-xs text-red-600 hidden">Please enter your full name.</p>
                    </div>
                </div>

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
                        <button type="button" onclick="togglePassword('password')"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-500">
                            <i class="fas fa-eye text-sm"></i>
                        </button>
                    </div>
                    <div id="passwordRequirements" class="mt-1.5 space-y-0.5">
                        <div id="lengthReq" class="flex items-center text-xs text-gray-500">
                            <i class="fas fa-circle mr-1.5"></i>
                            <span>At least 8 characters</span>
                        </div>
                        <div id="upperReq" class="flex items-center text-xs text-gray-500">
                            <i class="fas fa-circle mr-1.5"></i>
                            <span>At least one uppercase letter</span>
                        </div>
                        <div id="lowerReq" class="flex items-center text-xs text-gray-500">
                            <i class="fas fa-circle mr-1.5"></i>
                            <span>At least one lowercase letter</span>
                        </div>
                        <div id="numberReq" class="flex items-center text-xs text-gray-500">
                            <i class="fas fa-circle mr-1.5"></i>
                            <span>At least one number</span>
                        </div>
                    </div>
                </div>

                <!-- Confirm Password -->
                <div>
                    <label for="confirmPassword" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                    <div class="mt-1 relative">
                        <input type="password" id="confirmPassword" name="confirmPassword" required
                               class="appearance-none block w-full px-3 py-1.5 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-secondary focus:border-secondary text-sm"
                               oninput="validateConfirmPassword(this)">
                        <button type="button" onclick="togglePassword('confirmPassword')"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-500">
                            <i class="fas fa-eye text-sm"></i>
                        </button>
                    </div>
                    <p id="confirmPasswordError" class="mt-1 text-xs text-red-600 hidden">Passwords do not match.</p>
                </div>

                <!-- System Name -->
                <div>
                    <label for="systemName" class="block text-sm font-medium text-gray-700">System Name</label>
                    <div class="mt-1">
                        <input type="text" id="systemName" name="systemName" required
                               class="appearance-none block w-full px-3 py-1.5 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-secondary focus:border-secondary text-sm"
                               oninput="validateSystemName(this)">
                        <p id="systemNameError" class="mt-1 text-xs text-red-600 hidden">Please enter a valid system name.</p>
                        <p class="mt-1 text-xs text-gray-500">A name to identify your application (e.g., "My Web App")</p>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button type="submit" id="submitBtn"
                            class="w-full flex justify-center py-1.5 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-secondary hover:bg-blue-600 focus:outline-none focus:ring-1 focus:ring-offset-1 focus:ring-secondary disabled:opacity-50 disabled:cursor-not-allowed">
                        <span id="submitText">Register</span>
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
                    Already have an account? 
                    <a href="login-page.php" class="font-medium text-secondary hover:text-blue-600">Login here</a>
                </p>
                <p class="text-xs text-gray-600">
                    <a href="../index.php" class="font-medium text-secondary hover:text-blue-600">Back to Documentation</a>
                </p>
            </div>
        </div>
    </div>

    <script>
        // Form elements
        const form = document.getElementById('registerForm');
        const fullNameInput = document.getElementById('fullName');
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirmPassword');
        const systemNameInput = document.getElementById('systemName');
        const submitBtn = document.getElementById('submitBtn');
        const submitText = document.getElementById('submitText');
        const submitSpinner = document.getElementById('submitSpinner');

        // Validation functions
        function validateFullName(input) {
            const isValid = input.value.trim().length >= 2 && /^[a-zA-Z\s'-]+$/.test(input.value);
            input.classList.toggle('border-red-300', !isValid && input.value);
            const errorElement = document.getElementById('fullNameError');
            errorElement.textContent = input.value ? (isValid ? '' : 'Please enter a valid full name') : 'Full name is required';
            errorElement.classList.toggle('hidden', isValid);
            updateSubmitButton();
            return isValid;
        }

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
            const requirements = {
                length: { regex: /.{8,}/, element: document.getElementById('lengthReq') },
                upper: { regex: /[A-Z]/, element: document.getElementById('upperReq') },
                lower: { regex: /[a-z]/, element: document.getElementById('lowerReq') },
                number: { regex: /[0-9]/, element: document.getElementById('numberReq') }
            };

            let isValid = true;
            const value = input.value;

            // Check each requirement
            for (const [key, req] of Object.entries(requirements)) {
                if (req.regex.test(value)) {
                    req.element.classList.add('text-green-600');
                    req.element.classList.remove('text-gray-500');
                    req.element.querySelector('i').className = 'fas fa-check-circle mr-2';
                } else {
                    req.element.classList.add('text-gray-500');
                    req.element.classList.remove('text-green-600');
                    req.element.querySelector('i').className = 'fas fa-circle mr-2';
                    isValid = false;
                }
            }

            input.classList.toggle('border-red-300', !isValid && value);
            validateConfirmPassword(confirmPasswordInput);
            updateSubmitButton();
            return isValid;
        }

        function validateConfirmPassword(input) {
            const isValid = input.value === passwordInput.value;
            input.classList.toggle('border-red-300', !isValid && input.value);
            const errorElement = document.getElementById('confirmPasswordError');
            errorElement.textContent = input.value ? (isValid ? '' : 'Passwords do not match') : 'Please confirm your password';
            errorElement.classList.toggle('hidden', isValid);
            updateSubmitButton();
            return isValid;
        }

        function validateSystemName(input) {
            const isValid = input.value.trim().length >= 3 && /^[a-zA-Z0-9\s-_]+$/.test(input.value);
            input.classList.toggle('border-red-300', !isValid && input.value);
            const errorElement = document.getElementById('systemNameError');
            errorElement.textContent = input.value ? (isValid ? '' : 'System name can only contain letters, numbers, spaces, hyphens, and underscores') : 'System name is required';
            errorElement.classList.toggle('hidden', isValid);
            updateSubmitButton();
            return isValid;
        }

        function updateSubmitButton() {
            const isValid = !fullNameInput.classList.contains('border-red-300') &&
                          !emailInput.classList.contains('border-red-300') &&
                          !passwordInput.classList.contains('border-red-300') &&
                          !confirmPasswordInput.classList.contains('border-red-300') &&
                          !systemNameInput.classList.contains('border-red-300') &&
                          fullNameInput.value &&
                          emailInput.value &&
                          passwordInput.value &&
                          confirmPasswordInput.value &&
                          systemNameInput.value;
            submitBtn.disabled = !isValid;
        }

        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const type = input.type === 'password' ? 'text' : 'password';
            input.type = type;
            const icon = type === 'password' ? 'fa-eye' : 'fa-eye-slash';
            input.nextElementSibling.querySelector('i').className = `fas ${icon}`;
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
            window.location.href = 'dashboard.html';
        }

        // Form submission
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            hideError();

            // Validate all fields
            const isFullNameValid = validateFullName(fullNameInput);
            const isEmailValid = validateEmail(emailInput);
            const isPasswordValid = validatePassword(passwordInput);
            const isConfirmPasswordValid = validateConfirmPassword(confirmPasswordInput);
            const isSystemNameValid = validateSystemName(systemNameInput);

            if (!isFullNameValid || !isEmailValid || !isPasswordValid || 
                !isConfirmPasswordValid || !isSystemNameValid) {
                showError('Please fix the validation errors before submitting.');
                return;
            }

            showLoading();

            try {
                const response = await fetch('register.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        fullName: fullNameInput.value.trim(),
                        email: emailInput.value.trim(),
                        password: passwordInput.value,
                        systemName: systemNameInput.value.trim()
                    })
                });

                const data = await response.json();

                if (data.success) {
                    showSuccess(`Registration successful! Your API key is: ${data.api_key}\n\nRedirecting to login...`);
                    
                    // Clear form
                    form.reset();
                    
                    // Redirect to login
                    setTimeout(() => {
                        window.location.href = 'login-page.php';
                    }, 5000);
                } else {
                    showError(
                        data.error || 'Registration failed',
                        data.debug_info ? `Debug info: ${JSON.stringify(data.debug_info)}` : ''
                    );
                }
            } catch (error) {
                showError(
                    'Network error occurred',
                    'Please check your internet connection and try again.'
                );
                console.error('Registration error:', error);
            } finally {
                hideLoading();
            }
        });

        // Initialize form validation
        fullNameInput.addEventListener('input', () => validateFullName(fullNameInput));
        emailInput.addEventListener('input', () => validateEmail(emailInput));
        passwordInput.addEventListener('input', () => validatePassword(passwordInput));
        confirmPasswordInput.addEventListener('input', () => validateConfirmPassword(confirmPasswordInput));
        systemNameInput.addEventListener('input', () => validateSystemName(systemNameInput));
    </script>
</body>
</html> 