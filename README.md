# Authentication API System

A secure, modular, and scalable authentication API built with pure PHP and MySQL. This system provides both a developer portal for managing API keys and a complete authentication API that can be integrated into any application.

## Features

### Developer Portal
- Developer registration with email verification
- Secure login system
- API key management
- Interactive documentation
- Modern UI with Tailwind CSS

### Authentication API
- User registration with email verification
- Secure token-based authentication
- Password reset functionality
- Password change functionality
- Secure token management
- Rate limiting and API key validation

## Setup Instructions

1. Clone the repository:
```bash
git clone <repository-url>
cd user_auth_api
```

2. Install dependencies:
```bash
composer install
```

3. Create a MySQL database and import the schema:
```bash
mysql -u root -p < database/schema.sql
```

4. Configure your environment:
   - Copy `.env.example` to `.env`
   - Update database credentials
   - Configure SMTP settings for email
   - Set security keys

5. Configure your web server:
   - Point document root to the project directory
   - Ensure PHP has write permissions for logs
   - Enable URL rewriting (if using Apache)

## API Documentation

### Developer Endpoints

#### Register Developer
```http
POST /dev/register.php
Content-Type: application/json

{
    "email": "dev@example.com",
    "password": "SecurePass123",
    "fullName": "John Doe",
    "systemName": "My App"
}
```

#### Developer Login
```http
POST /dev/login.php
Content-Type: application/json

{
    "email": "dev@example.com",
    "password": "SecurePass123"
}

Response:
{
    "success": true,
    "message": "Login successful",
    "data": {
        "id": "dev_id",
        "email": "dev@example.com",
        "full_name": "John Doe",
        "is_email_verified": true,
        "api_key": "your_api_key",
        "system_name": "My App"
    }
}
```

### Authentication API Endpoints

All API endpoints require the `X-API-Key` header with a valid API key.

#### Register User
```http
POST /api/register.php
X-API-Key: your-api-key
Content-Type: application/json

{
    "email": "user@example.com",
    "password": "SecurePass123"
}

Response:
{
    "success": true,
    "message": "User registered successfully",
    "data": {
        "user_id": "user_id",
        "email": "user@example.com",
        "auth_token": "32_character_token",
        "expires_at": "token_expiry_timestamp",
        "is_verified": false,
        "otp": "verification_code"
    }
}
```

#### Request OTP
```http
POST /api/request-otp.php
X-API-Key: your-api-key
Content-Type: application/json

{
    "email": "user@example.com",
    "purpose": "email-verification" // or "password-reset"
}

Response:
{
    "success": true,
    "message": "OTP generated successfully",
    "data": {
        "user_id": "user_id",
        "email": "user@example.com",
        "purpose": "email-verification",
        "otp": "6_digit_code",
        "otp_expires_at": "otp_expiry_timestamp",
        "auth_token": "32_character_token",
        "token_expires_at": "token_expiry_timestamp"
    }
}
```

#### Verify Email
```http
POST /api/verify-email.php
X-API-Key: your-api-key
Content-Type: application/json

{
    "email": "user@example.com",
    "otp": "123456"
}

Response:
{
    "success": true,
    "message": "Email verified successfully",
    "data": {
        "user_id": "user_id",
        "email": "user@example.com",
        "auth_token": "32_character_token",
        "expires_at": "token_expiry_timestamp"
    }
}
```

#### User Login
```http
POST /api/login.php
X-API-Key: your-api-key
Content-Type: application/json

{
    "email": "user@example.com",
    "password": "SecurePass123"
}

Response:
{
    "success": true,
    "message": "Login successful",
    "data": {
        "user_id": "user_id",
        "email": "user@example.com",
        "auth_token": "32_character_token",
        "expires_at": "token_expiry_timestamp"
    }
}
```

#### Reset Password
```http
POST /api/reset-password.php
X-API-Key: your-api-key
Authorization: Bearer auth_token
Content-Type: application/json

{
    "otp": "123456",
    "new_password": "NewSecurePass123"
}

Response:
{
    "success": true,
    "message": "Password reset successfully",
    "data": {
        "email": "user@example.com",
        "token_expires_at": "token_expiry_timestamp"
    }
}
```

#### Change Password
```http
POST /api/change-password.php
X-API-Key: your-api-key
Authorization: Bearer auth_token
Content-Type: application/json

{
    "old_password": "CurrentPass123",
    "new_password": "NewSecurePass123",
    "confirm_password": "NewSecurePass123"
}

Response:
{
    "success": true,
    "message": "Password changed successfully"
}
```

#### Logout
```http
POST /api/logout.php
X-API-Key: your-api-key
Authorization: Bearer auth_token
Content-Type: application/json

Response:
{
    "success": true,
    "message": "Successfully logged out"
}
```

#### Refresh Token
```http
POST /api/refresh-token.php
X-API-Key: your-api-key
Authorization: Bearer auth_token
Content-Type: application/json

Response:
{
    "success": true,
    "message": "Token refreshed successfully",
    "data": {
        "token": "new_32_character_token",
        "expires_at": "token_expiry_timestamp"
    }
}
```

#### Delete User
```http
POST /api/delete-user.php
X-API-Key: your-api-key
Content-Type: application/json

{
    "email": "user@example.com"
}

Response:
{
    "success": true,
    "message": "User account deleted successfully",
    "data": {
        "email": "user@example.com"
    }
}
```

## Security Features

- Password hashing using PHP's native `password_hash()`
- Secure token-based authentication
- API key validation
- Email verification
- OTP for password reset
- Input sanitization
- SQL injection prevention using prepared statements
- XSS protection
- Rate limiting
- Secure session management

## Database Schema

The system uses the following tables:

- `dev_accounts`: Stores developer users
- `api_clients`: Links developers to their API keys
- `email_otps`: Manages email verification codes
- `api_users`: Stores end-users of client applications
- `auth_tokens`: Manages authentication tokens
- `password_reset_otps`: Handles password reset flow

## Contributing

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a new Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details. 