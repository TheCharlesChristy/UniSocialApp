# Authentication API Documentation

## Overview

This API provides comprehensive user authentication functionality including user registration, login, password management, and email verification. The API uses JWT (JSON Web Tokens) for authentication with token blacklisting support for secure logout functionality.

**Base URL:** `/api/auth/`  
**Content Type:** `application/json`  
**Authentication:** Bearer token (where required)

## Table of Contents

- [Authentication Flow](#authentication-flow)
- [Endpoints](#endpoints)
  - [POST /register](#post-register)
  - [POST /login](#post-login)
  - [POST /logout](#post-logout)
  - [POST /forgot-password](#post-forgot-password)
  - [POST /reset-password](#post-reset-password)
  - [GET /verify-email/{token}](#get-verify-emailtoken)
- [Data Models](#data-models)
- [Error Handling](#error-handling)
- [Security Features](#security-features)

## Authentication Flow

1. **Registration:** User registers with email verification
2. **Email Verification:** User clicks verification link to activate account
3. **Login:** User authenticates and receives JWT token
4. **Protected Requests:** Include `Authorization: Bearer {token}` header
5. **Logout:** Token is blacklisted for security
6. **Password Reset:** Email-based password reset with secure tokens

## Endpoints

### POST /register

Creates a new user account and sends email verification.

**Authentication:** None required

**Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| username | string | Yes | Unique username (alphanumeric) |
| email | string | Yes | Valid email address |
| password | string | Yes | Password (min 8 chars, letters + numbers) |
| first_name | string | Yes | User's first name |
| last_name | string | Yes | User's last name |
| date_of_birth | string | Yes | Date of birth (YYYY-MM-DD format) |

**Request Body:**
```json
{
  "username": "johndoe",
  "email": "john@example.com",
  "password": "mypassword123",
  "first_name": "John",
  "last_name": "Doe",
  "date_of_birth": "1990-01-15"
}
```

**Response:**
- **201 Created** - User registered successfully
- **400 Bad Request** - Invalid input data
- **409 Conflict** - Username or email already exists

**Example Response:**
```json
{
  "success": true,
  "message": "User registered successfully. Check your email to verify your account.",
  "user_id": 123
}
```

---

### POST /login

Authenticates user and returns access token.

**Authentication:** None required

**Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| email | string | Yes* | User's email address |
| username | string | Yes* | User's username |
| password | string | Yes | User's password |

*Either email or username is required

**Request Body:**
```json
{
  "email": "john@example.com",
  "password": "mypassword123"
}
```

**Response:**
- **200 OK** - Login successful
- **400 Bad Request** - Missing required fields
- **401 Unauthorized** - Invalid credentials or inactive account

**Example Response:**
```json
{
  "success": true,
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "user_id": 123,
  "expiration": "2024-01-16T10:30:00+00:00"
}
```

---

### POST /logout

Invalidates current user token by adding it to blacklist.

**Authentication:** Required (Bearer token)

**Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| token | string | Yes | JWT token to invalidate |

**Request Body:**
```json
{
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
}
```

**Response:**
- **200 OK** - Logout successful
- **400 Bad Request** - Token not provided
- **401 Unauthorized** - Invalid or expired token

**Example Response:**
```json
{
  "success": true,
  "message": "Successfully logged out"
}
```

---

### POST /forgot-password

Initiates password reset process by sending email with reset token.

**Authentication:** None required

**Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| email | string | Yes | User's email address |

**Request Body:**
```json
{
  "email": "john@example.com"
}
```

**Response:**
- **200 OK** - Reset email sent (or would be sent)
- **400 Bad Request** - Invalid email format

**Example Response:**
```json
{
  "success": true,
  "message": "If your email is registered, you will receive password reset instructions"
}
```

**Security Note:** This endpoint always returns success to prevent email enumeration attacks.

---

### POST /reset-password

Updates password using reset token received via email.

**Authentication:** None required (uses reset token)

**Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| token | string | Yes | Password reset token from email |
| new_password | string | Yes | New password (min 8 chars, letters + numbers) |

**Request Body:**
```json
{
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "new_password": "mynewpassword123"
}
```

**Response:**
- **200 OK** - Password reset successful
- **400 Bad Request** - Invalid input or weak password
- **401 Unauthorized** - Invalid or expired token
- **404 Not Found** - User not found

**Example Response:**
```json
{
  "success": true,
  "message": "Password has been reset successfully"
}
```

---

### GET /verify-email/{token}

Verifies user email using verification token.

**Authentication:** None required (uses verification token)

**URL Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| token | string | Yes | Email verification token |

**Example Request:**
```
GET /api/auth/verify-email/eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
```

**Response:**
- **200 OK** - Email verified successfully
- **400 Bad Request** - Token not provided
- **401 Unauthorized** - Invalid or expired token
- **404 Not Found** - User not found

**Example Response:**
```json
{
  "success": true,
  "message": "Email verified successfully"
}
```

## Data Models

### User Object
```json
{
  "user_id": 123,
  "username": "johndoe",
  "email": "john@example.com",
  "first_name": "John",
  "last_name": "Doe",
  "account_status": "active",
  "role": "user",
  "registration_date": "2024-01-15T10:30:00Z",
  "last_login": "2024-01-15T14:20:00Z"
}
```

### JWT Token Payload
```json
{
  "iat": 1642248600,
  "exp": 1642335000,
  "user_id": 123,
  "type": "auth",
  "jti": "unique-token-id"
}
```

## Error Handling

All error responses follow this format:

```json
{
  "success": false,
  "message": "Error description"
}
```

### Common HTTP Status Codes

- **400 Bad Request** - Invalid input data or malformed request
- **401 Unauthorized** - Authentication required or invalid credentials
- **405 Method Not Allowed** - HTTP method not supported
- **409 Conflict** - Resource already exists (username/email taken)
- **500 Internal Server Error** - Database or server error

### Authentication Errors

When using protected endpoints, include the Authorization header:

```
Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
```

Common authentication error messages:
- "Authentication required"
- "Invalid authentication format"
- "Invalid or expired token"
- "Token has been invalidated"
- "Account is not active"

## Security Features

### Token Management
- **JWT Tokens** with HMAC SHA-256 signing
- **Token Expiration**: 24 hours for access tokens, 1 hour for reset tokens, 48 hours for verification tokens
- **Token Blacklisting** for secure logout
- **Automatic Cleanup** of expired blacklisted tokens

### Password Security
- **Bcrypt Hashing** with cost factor 12
- **Password Requirements**: Minimum 8 characters with letters and numbers
- **Secure Reset Process** with time-limited tokens

### Anti-Enumeration
- Consistent responses for non-existent emails in forgot-password
- Generic error messages to prevent user enumeration

### Database Security
- **Prepared Statements** to prevent SQL injection
- **Transaction Support** for data consistency
- **Connection Validation** before processing requests

## Implementation Notes

### Email Integration
Currently, email functionality logs to `email_logs.txt` for development. In production, integrate with:
- SendGrid, Mailgun, or similar email service
- SMTP server configuration
- HTML email templates

### Database Schema
Required tables:
- `users` - User account information
- `token_blacklist` - Invalidated tokens

### Configuration
Update the secret key in `AuthUtils` class before production deployment:
```php
private static $secretKey = 'your-secure-256-bit-secret-key';
```