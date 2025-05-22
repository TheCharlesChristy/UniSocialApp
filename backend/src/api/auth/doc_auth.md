# Authentication API Documentation

This document provides detailed information about the authentication endpoints available in the application.

## Overview

The authentication system provides a complete set of endpoints for user registration, login, logout, email verification, and password management. It uses JWT (JSON Web Tokens) for secure authentication and maintains a token blacklist for logout functionality.

## Base URL

All endpoints are relative to the base API URL: `/api/auth/`

## Authentication Endpoints

### 1. Register New User

Creates a new user account and sends a verification email.

- **URL**: `/api/auth/register`
- **Method**: `POST`
- **Access**: Public
- **Content-Type**: `application/json`

**Request Body:**

```json
{
  "username": "johndoe",
  "email": "john.doe@example.com",
  "password": "SecureP@ss123",
  "first_name": "John",
  "last_name": "Doe",
  "date_of_birth": "1990-01-01"
}
```

**Response:**

```json
{
  "success": true,
  "message": "User registered successfully. Check your email to verify your account.",
  "user_id": 123
}
```

**Error Responses:**

- `400 Bad Request` - Missing or invalid input parameters
- `409 Conflict` - Username or email already exists
- `500 Internal Server Error` - Server error

### 2. User Login

Authenticates user and returns access token.

- **URL**: `/api/auth/login`
- **Method**: `POST`
- **Access**: Public
- **Content-Type**: `application/json`

**Request Body:**

```json
{
  "email": "john.doe@example.com",
  "password": "SecureP@ss123"
}
```

OR

```json
{
  "username": "johndoe",
  "password": "SecureP@ss123"
}
```

**Response:**

```json
{
  "success": true,
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "user_id": 123,
  "expiration": "2023-05-23T10:30:00+00:00"
}
```

**Error Responses:**

- `400 Bad Request` - Missing or invalid input parameters
- `401 Unauthorized` - Invalid credentials or account not active
- `500 Internal Server Error` - Server error

### 3. User Logout

Invalidates current user token by adding it to blacklist.

- **URL**: `/api/auth/logout`
- **Method**: `POST`
- **Access**: Authenticated users
- **Content-Type**: `application/json`

**Request Body:**

```json
{
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
}
```

**Response:**

```json
{
  "success": true,
  "message": "Successfully logged out"
}
```

**Error Responses:**

- `400 Bad Request` - Missing token
- `401 Unauthorized` - Invalid token or already invalidated
- `500 Internal Server Error` - Server error

### 4. Forgot Password

Initiates password reset process by sending an email with reset token.

- **URL**: `/api/auth/forgot-password`
- **Method**: `POST`
- **Access**: Public
- **Content-Type**: `application/json`

**Request Body:**

```json
{
  "email": "john.doe@example.com"
}
```

**Response:**

```json
{
  "success": true,
  "message": "If your email is registered, you will receive password reset instructions"
}
```

**Error Responses:**

- `400 Bad Request` - Missing or invalid email
- `500 Internal Server Error` - Server error (e.g., email sending failure)

### 5. Reset Password

Updates password using reset token.

- **URL**: `/api/auth/reset-password`
- **Method**: `POST`
- **Access**: Public (with valid token)
- **Content-Type**: `application/json`

**Request Body:**

```json
{
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "new_password": "NewSecureP@ss456"
}
```

**Response:**

```json
{
  "success": true,
  "message": "Password has been reset successfully"
}
```

**Error Responses:**

- `400 Bad Request` - Missing token/password or password too weak
- `401 Unauthorized` - Invalid or expired token
- `500 Internal Server Error` - Server error

### 6. Verify Email

Verifies user email using token from verification email.

- **URL**: `/api/auth/verify-email/:token`
- **Method**: `GET`
- **Access**: Public (with valid token)

**URL Parameters:**

- `token` - Email verification token

**Response:**

```json
{
  "success": true,
  "message": "Email verified successfully"
}
```

**Error Responses:**

- `401 Unauthorized` - Invalid or expired token
- `500 Internal Server Error` - Server error

## Using Authentication in Other Endpoints

To protect your API endpoints with authentication, include the following code at the beginning of your PHP file:

```php
// Require authentication
$requireAuth = true;

// Get database connection
$Database = require_once dirname(dirname(dirname(__FILE__))) . '/db_handler/connection.php';

// Include authentication middleware (this will exit if not authorized)
require_once dirname(__FILE__) . '/../auth/auth_middleware.php';

// Now $authUser contains authenticated user data
// You can access user properties like $authUser['user_id'], $authUser['username'], etc.
```

## Token Usage

When a user is authenticated, the client should:

1. Store the JWT token securely (preferably in memory for SPA applications)
2. Include the token in subsequent API requests in the Authorization header:
   
   ```
   Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
   ```

## Security Considerations

1. Always use HTTPS in production
2. Change the secret key in auth_utils.php for production
3. Implement proper email sending functionality in production
4. Consider adding rate limiting to prevent brute force attacks
5. Monitor the token blacklist table size and cleanup expired tokens regularly

## Development Notes

In the development environment:
- Email sending is simulated (tokens are logged to `email_logs.txt`)
- The token blacklist table is periodically cleaned up (10% chance on each logout)

## Troubleshooting

Common issues:

1. **"Token invalid" errors**: Check token expiration (default is 24 hours)
2. **CORS issues**: Add appropriate headers for your frontend domain
3. **DB connection issues**: Check database credentials and connectivity
4. **Email verification failures**: Check logs for token information
