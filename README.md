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
- JWT-based authentication
- Password reset via email
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
   - Set JWT secret key

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

#### Verify Developer Email
```http
POST /dev/verify-email.php
Content-Type: application/json

{
    "email": "dev@example.com",
    "otp": "123456"
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
```

#### Verify User Email
```http
POST /api/verify-email.php
X-API-Key: your-api-key
Content-Type: application/json

{
    "email": "user@example.com",
    "otp": "123456"
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
```

#### Request Password Reset
```http
POST /api/reset-password.php
X-API-Key: your-api-key
Content-Type: application/json

{
    "email": "user@example.com"
}
```

#### Complete Password Reset
```http
POST /api/reset-password.php
X-API-Key: your-api-key
Content-Type: application/json

{
    "email": "user@example.com",
    "otp": "123456",
    "new_password": "NewSecurePass123"
}
```

#### Change Password (Authenticated)
```http
POST /api/change-password.php
X-API-Key: your-api-key
Authorization: Bearer user-jwt-token
Content-Type: application/json

{
    "current_password": "CurrentPass123",
    "new_password": "NewSecurePass123"
}
```

## Security Features

- Password hashing using PHP's native `password_hash()`
- JWT tokens for authentication
- API key validation
- Email verification
- OTP for password reset
- Input sanitization
- SQL injection prevention using prepared statements
- XSS protection
- CSRF protection (for web forms)
- Rate limiting
- Secure session management

## Database Schema

The system uses the following tables:

- `dev_accounts`: Stores developer users
- `api_clients`: Links developers to their API keys
- `email_otps`: Manages email verification and reset codes
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