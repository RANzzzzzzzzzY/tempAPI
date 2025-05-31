import requests

class UserAuthAPI:
    def __init__(self, api_key):
        self.api_key = api_key
        self.base_url = 'https://morales.infy.uk/user_auth_api1'
        self.auth_token = None

    def send_request(self, endpoint, data, include_token=False):
        headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-API-Key': self.api_key
        }

        if include_token and self.auth_token:
            headers['Authorization'] = f'Bearer {self.auth_token}'

        try:
            response = requests.post(
                f"{self.base_url}{endpoint}",
                json=data,
                headers=headers
            )
            return {
                'status': response.status_code,
                'data': response.json()
            }
        except Exception as e:
            return {
                'status': 500,
                'data': {
                    'success': False,
                    'error': str(e)
                }
            }

    # New method for token validation
    def validate_token(self):
        return self.send_request('/api/validate-token.php', {}, True)

    # ... (rest of the existing methods)

# Usage Example:
auth = UserAuthAPI('your_api_key_here')

# Login first to get a token
login_response = auth.login('user@example.com', 'password123')

if login_response['status'] == 200:
    # Validate the token
    validation_response = auth.validate_token()
    if validation_response['status'] == 200:
        print(f"Token is valid. User: {validation_response['data']['data']['email']}")
    else:
        print(f"Token validation failed: {validation_response['data']['message']}") 