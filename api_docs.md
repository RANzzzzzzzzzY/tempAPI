# User Auth API Documentation

Base URL: `https://morales.infy.uk/user_auth_api1`

## Developer Endpoints

### Register Developer
- **URL**: `/dev/register.php`
- **Method**: `POST`
- **Headers**:
  ```
  Content-Type: application/json
  Accept: application/json
  ```
- **Body**:
  ```json
  {
    "fullName": "Your Name",
    "email": "your.email@example.com",
    "password": "your_password",
    "systemName": "Your App Name"
  }
  ```
- **Success Response**:
  ```json
  {
    "success": true,
    "message": "Registration successful!",
    "api_key": "ak_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
  }
  ```

### Developer Login
- **URL**: `/dev/login.php`
- **Method**: `POST`
- **Headers**:
  ```
  Content-Type: application/json
  Accept: application/json
  ```
- **Body**:
  ```json
  {
    "email": "your.email@example.com",
    "password": "your_password"
  }
  ```
- **Success Response**:
  ```json
  {
    "success": true,
    "message": "Login successful",
    "data": {
      "id": "1",
      "email": "your.email@example.com",
      "full_name": "Your Name",
      "is_email_verified": true,
      "api_key": "ak_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
      "system_name": "Your App Name"
    }
  }
  ```

## API User Endpoints

### Register User
- **URL**: `/api/register.php`
- **Method**: `POST`
- **Headers**:
  ```
  Content-Type: application/json
  Accept: application/json
  X-API-Key: your_api_key_here
  ```
- **Body**:
  ```json
  {
    "email": "user@example.com",
    "password": "user_password"
  }
  ```
- **Success Response**:
  ```json
  {
    "success": true,
    "message": "User registered successfully",
    "data": {
      "user_id": "1",
      "email": "user@example.com",
      "verification_token": "verification_token_string"
    }
  }
  ```

### User Login
- **URL**: `/api/login.php`
- **Method**: `POST`
- **Headers**:
  ```
  Content-Type: application/json
  Accept: application/json
  X-API-Key: your_api_key_here
  ```
- **Body**:
  ```json
  {
    "email": "user@example.com",
    "password": "user_password"
  }
  ```
- **Success Response**:
  ```json
  {
    "success": true,
    "message": "Login successful",
    "data": {
      "user_id": "1",
      "email": "user@example.com",
      "auth_token": "auth_token_string",
      "expires_at": "2024-04-20 12:00:00"
    }
  }
  ```

## Common Error Responses
```json
{
  "success": false,
  "error": "Error message here"
}
```

## Testing in Postman
1. Create a new request
2. Set the request URL to the full endpoint path (e.g., `https://morales.infy.uk/user_auth_api1/api/register.php`)
3. Set method to POST
4. Add headers:
   - Content-Type: application/json
   - Accept: application/json
   - X-API-Key: your_api_key_here (for API user endpoints)
5. In the Body tab:
   - Select "raw"
   - Select "JSON" from the dropdown
   - Enter the request body as shown in the examples above
6. Send the request

## Important Notes
1. For API user endpoints (`/api/*`), you must include your API key in the `X-API-Key` header
2. API keys are obtained by registering as a developer
3. Each API user is associated with the developer account that created them
4. Email verification is required before users can log in 