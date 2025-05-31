document.addEventListener('DOMContentLoaded', () => {
    // Verify configuration
    if (!window.appConfig) {
        console.error('Missing application configuration');
        return;
    }

    // Modal handling
    const loginBtn = document.getElementById('loginBtn');
    const registerBtn = document.getElementById('registerBtn');
    const getStartedBtn = document.getElementById('getStartedBtn');
    const loginModal = document.getElementById('loginModal');
    const registerModal = document.getElementById('registerModal');
    const closeButtons = document.querySelectorAll('.closeModal');

    if (!loginBtn || !registerBtn || !getStartedBtn || !loginModal || !registerModal) {
        console.error('Required elements not found');
        return;
    }

    // Show modals
    loginBtn.addEventListener('click', (e) => {
        e.preventDefault();
        loginModal.classList.remove('hidden');
    });

    registerBtn.addEventListener('click', (e) => {
        e.preventDefault();
        registerModal.classList.remove('hidden');
    });

    getStartedBtn.addEventListener('click', (e) => {
        e.preventDefault();
        registerModal.classList.remove('hidden');
    });

    // Close modals
    closeButtons.forEach(button => {
        button.addEventListener('click', () => {
            loginModal.classList.add('hidden');
            registerModal.classList.add('hidden');
        });
    });

    // Close on outside click
    window.addEventListener('click', (e) => {
        if (e.target === loginModal || e.target === registerModal) {
            loginModal.classList.add('hidden');
            registerModal.classList.add('hidden');
        }
    });

    // Form submissions
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');

    loginForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(loginForm);
        
        try {
            const response = await fetch(`${window.appConfig.baseUrl}/dev/login.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    email: formData.get('email'),
                    password: formData.get('password')
                })
            });

            const data = await response.json();
            
            if (response.ok) {
                window.location.href = `${window.appConfig.baseUrl}/dev/dashboard.php`;
            } else {
                alert(data.error || 'Login failed');
            }
        } catch (error) {
            console.error('Login error:', error);
            alert('An error occurred during login');
        }
    });

    registerForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(registerForm);
        
        try {
            const response = await fetch(`${window.appConfig.baseUrl}/dev/register.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    fullName: formData.get('fullName'),
                    email: formData.get('email'),
                    systemName: formData.get('systemName'),
                    password: formData.get('password')
                })
            });

            const data = await response.json();
            
            if (response.ok) {
                alert('Registration successful! Your API key is: ' + data.api_key);
                registerModal.classList.add('hidden');
                registerForm.reset();
            } else {
                alert(data.error || 'Registration failed');
            }
        } catch (error) {
            console.error('Registration error:', error);
            alert('An error occurred during registration');
        }
    });
}); 