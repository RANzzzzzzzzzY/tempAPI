class UserAuthAPI {
    constructor(apiKey) {
        this.apiKey = apiKey;
        this.baseUrl = 'https://morales.infy.uk/user_auth_api1';
        this.authToken = null;
    }

    async sendRequest(endpoint, data, includeToken = false) {
        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-API-Key': this.apiKey
        };

        if (includeToken && this.authToken) {
            headers['Authorization'] = `Bearer ${this.authToken}`;
        }

        try {
            const response = await fetch(this.baseUrl + endpoint, {
                method: 'POST',
                headers: headers,
                body: JSON.stringify(data)
            });

            const responseData = await response.json();
            return {
                status: response.status,
                data: responseData
            };
        } catch (error) {
            return {
                status: 500,
                data: {
                    success: false,
                    error: error.message
                }
            };
        }
    }

    // New method for token validation
    async validateToken() {
        return await this.sendRequest('/api/validate-token.php', {}, true);
    }

    // ... (rest of the existing methods)
}

// Usage Example:
const auth = new UserAuthAPI('your_api_key_here');

async function example() {
    try {
        // Login first to get a token
        const loginResponse = await auth.login('user@example.com', 'password123');
        
        if (loginResponse.status === 200) {
            // Validate the token
            const validationResponse = await auth.validateToken();
            if (validationResponse.status === 200) {
                console.log('Token is valid. User:', validationResponse.data.data.email);
            } else {
                console.log('Token validation failed:', validationResponse.data.message);
            }
        }
    } catch (error) {
        console.error('Error:', error);
    }
} 