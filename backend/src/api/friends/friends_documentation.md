# Friends API Documentation

## Overview
The Friends API provides comprehensive functionality for managing friend relationships within the application. This includes sending friend requests, accepting/rejecting requests, viewing friends lists, and managing existing friendships.

**Base URL:** `/api/friends`  
**Authentication:** Bearer token required for all endpoints  
**Content-Type:** `application/json`

## Table of Contents
- [Authentication](#authentication)
- [Data Models](#data-models)
- [Endpoints](#endpoints)
  - [Get Friends List](#get-friends-list)
  - [Send Friend Request](#send-friend-request)
  - [Get Friend Requests](#get-friend-requests)
  - [Get Outgoing Requests](#get-outgoing-requests)
  - [Accept Friend Request](#accept-friend-request)
  - [Reject Friend Request](#reject-friend-request)
  - [Remove Friend](#remove-friend)
  - [Remove Friend Request](#remove-friend-request)
- [Error Handling](#error-handling)

## Authentication
All endpoints require authentication via Bearer token in the Authorization header:
```
Authorization: Bearer <your_token_here>
```

## Data Models

### User Object
```json
{
  "user_id": 123,
  "username": "john_doe",
  "first_name": "John",
  "last_name": "Doe",
  "profile_picture": "https://example.com/avatar.jpg",
  "bio": "Software developer"
}
```

### Friend Object
```json
{
  "user_id": 123,
  "username": "john_doe",
  "first_name": "John",
  "last_name": "Doe",
  "profile_picture": "https://example.com/avatar.jpg",
  "bio": "Software developer",
  "friends_since": "2024-01-15 10:30:00"
}
```

### Friend Request Object
```json
{
  "user_id": 123,
  "username": "john_doe",
  "first_name": "John",
  "last_name": "Doe",
  "profile_picture": "https://example.com/avatar.jpg",
  "bio": "Software developer",
  "request_date": "2024-01-15 10:30:00"
}
```

### Pagination Object
```json
{
  "current_page": 1,
  "total_pages": 5,
  "total_requests": 42,
  "per_page": 10
}
```

## Endpoints

### Get Friends List
Retrieves the current user's friends list with pagination support.

**Endpoint:** `GET /api/friends`

**Authentication:** Required

**Query Parameters:**
| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| page | integer | No | 1 | Page number for pagination |
| limit | integer | No | 20 | Number of friends per page (max 50) |

**Response Codes:**
- **200 OK** - Friends list retrieved successfully
- **401 Unauthorized** - Invalid or missing authentication token
- **500 Internal Server Error** - Database error

**Example Request:**
```bash
curl -X GET "https://api.example.com/api/friends?page=1&limit=10" \
  -H "Authorization: Bearer <token>"
```

**Example Response:**
```json
{
  "success": true,
  "friends": [
    {
      "user_id": 123,
      "username": "john_doe",
      "first_name": "John",
      "last_name": "Doe",
      "profile_picture": "https://example.com/avatar.jpg",
      "bio": "Software developer",
      "friends_since": "2024-01-15 10:30:00"
    }
  ],
  "total_friends": 25,
  "current_page": 1,
  "total_pages": 3
}
```

---

### Send Friend Request
Sends a friend request to another user.

**Endpoint:** `POST /api/friends/request` or `POST /api/friends/request/{userId}`

**Authentication:** Required

**Request Body:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| user_id | integer | Yes* | Target user ID (*not required if provided in URL) |

**Alternative URL Parameter:**
- `{userId}` - Target user ID in the URL path

**Response Codes:**
- **200 OK** - Friend request sent successfully
- **400 Bad Request** - Invalid user ID, self-request, or friendship already exists
- **403 Forbidden** - User is blocked or cannot send request
- **404 Not Found** - Target user not found
- **500 Internal Server Error** - Database error

**Example Request:**
```bash
curl -X POST "https://api.example.com/api/friends/request" \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{"user_id": 123}'
```

**Example Response:**
```json
{
  "success": true,
  "message": "Friend request sent successfully",
  "target_user": {
    "user_id": 123,
    "username": "john_doe"
  }
}
```

**Error Examples:**
```json
{
  "success": false,
  "message": "You are already friends with this user"
}
```

---

### Get Friend Requests
Retrieves pending friend requests (received or sent).

**Endpoint:** `GET /api/friends/requests`

**Authentication:** Required

**Query Parameters:**
| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| type | string | No | "received" | Type of requests ("received" or "sent") |
| page | integer | No | 1 | Page number for pagination |
| limit | integer | No | 20 | Number of requests per page (max 50) |

**Response Codes:**
- **200 OK** - Friend requests retrieved successfully
- **400 Bad Request** - Invalid type parameter
- **401 Unauthorized** - Invalid or missing authentication token
- **500 Internal Server Error** - Database error

**Example Request:**
```bash
curl -X GET "https://api.example.com/api/friends/requests?type=received&page=1&limit=10" \
  -H "Authorization: Bearer <token>"
```

**Example Response:**
```json
{
  "success": true,
  "type": "received",
  "requests": [
    {
      "user_id": 123,
      "username": "john_doe",
      "first_name": "John",
      "last_name": "Doe",
      "profile_picture": "https://example.com/avatar.jpg",
      "bio": "Software developer",
      "request_date": "2024-01-15 10:30:00"
    }
  ],
  "total_requests": 5,
  "current_page": 1,
  "total_pages": 1
}
```

---

### Get Outgoing Requests
Retrieves detailed information about outgoing friend requests with enhanced user data.

**Endpoint:** `GET /api/friends/get_outgoing_requests`

**Authentication:** Required

**Query Parameters:**
| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| page | integer | No | 1 | Page number for pagination |
| limit | integer | No | 10 | Number of requests per page (max 50) |

**Response Codes:**
- **200 OK** - Outgoing requests retrieved successfully
- **401 Unauthorized** - Invalid or missing authentication token
- **500 Internal Server Error** - Database error

**Example Request:**
```bash
curl -X GET "https://api.example.com/api/friends/get_outgoing_requests?page=1&limit=5" \
  -H "Authorization: Bearer <token>"
```

**Example Response:**
```json
{
  "success": true,
  "data": {
    "requests": [
      {
        "friendship_id": 456,
        "recipient": {
          "user_id": 123,
          "username": "john_doe",
          "email": "john@example.com",
          "first_name": "John",
          "last_name": "Doe",
          "profile_picture": "https://example.com/avatar.jpg",
          "bio": "Software developer"
        },
        "created_at": "2024-01-15 10:30:00",
        "status": "pending"
      }
    ],
    "pagination": {
      "current_page": 1,
      "total_pages": 2,
      "total_requests": 8,
      "per_page": 5
    }
  }
}
```

---

### Accept Friend Request
Accepts a pending friend request from another user.

**Endpoint:** `PUT /api/friends/accept/{userId}`

**Authentication:** Required

**URL Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| userId | integer | Yes | ID of the user who sent the friend request |

**Response Codes:**
- **200 OK** - Friend request accepted successfully
- **400 Bad Request** - Invalid user ID
- **404 Not Found** - No pending friend request found
- **500 Internal Server Error** - Database error

**Example Request:**
```bash
curl -X PUT "https://api.example.com/api/friends/accept/123" \
  -H "Authorization: Bearer <token>"
```

**Example Response:**
```json
{
  "success": true,
  "message": "Friend request accepted successfully"
}
```

---

### Reject Friend Request
Rejects a pending friend request from another user.

**Endpoint:** `PUT /api/friends/reject/{userId}`

**Authentication:** Required

**URL Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| userId | integer | Yes | ID of the user who sent the friend request |

**Response Codes:**
- **200 OK** - Friend request rejected successfully
- **400 Bad Request** - Invalid user ID
- **404 Not Found** - No pending friend request found
- **500 Internal Server Error** - Database error

**Example Request:**
```bash
curl -X PUT "https://api.example.com/api/friends/reject/123" \
  -H "Authorization: Bearer <token>"
```

**Example Response:**
```json
{
  "success": true,
  "message": "Friend request rejected successfully"
}
```

---

### Remove Friend
Removes an existing friendship between the current user and another user.

**Endpoint:** `DELETE /api/friends/{userId}`

**Authentication:** Required

**URL Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| userId | integer | Yes | ID of the friend to remove |

**Response Codes:**
- **200 OK** - Friend removed successfully
- **400 Bad Request** - Invalid user ID
- **404 Not Found** - Friendship not found
- **500 Internal Server Error** - Database error

**Example Request:**
```bash
curl -X DELETE "https://api.example.com/api/friends/123" \
  -H "Authorization: Bearer <token>"
```

**Example Response:**
```json
{
  "success": true,
  "message": "Friend removed successfully"
}
```

---

### Remove Friend Request
Removes a pending friend request that was sent by the current user.

**Endpoint:** `DELETE /api/friends/request/{userId}`

**Authentication:** Required

**URL Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| userId | integer | Yes | ID of the user to whom the request was sent |

**Response Codes:**
- **200 OK** - Friend request removed successfully
- **400 Bad Request** - Invalid user ID or self-removal attempt
- **404 Not Found** - No pending friend request found to remove
- **500 Internal Server Error** - Database error

**Example Request:**
```bash
curl -X DELETE "https://api.example.com/api/friends/request/123" \
  -H "Authorization: Bearer <token>"
```

**Example Response:**
```json
{
  "success": true,
  "message": "Friend request removed successfully"
}
```

## Error Handling

### Common Error Response Format
All endpoints return errors in a consistent format:

```json
{
  "success": false,
  "message": "Error description"
}
```

### HTTP Status Codes
- **200 OK** - Request successful
- **400 Bad Request** - Invalid input data or business logic violation
- **401 Unauthorized** - Authentication required or invalid token
- **403 Forbidden** - Access denied (e.g., blocked users)
- **404 Not Found** - Resource not found
- **405 Method Not Allowed** - HTTP method not supported
- **500 Internal Server Error** - Server error

### Common Error Messages
- `"Method not allowed"` - Incorrect HTTP method used
- `"Invalid user ID"` - User ID is missing or not numeric
- `"User not found"` - Target user doesn't exist or is inactive
- `"No pending friend request found"` - Request doesn't exist or already processed
- `"You are already friends with this user"` - Friendship already exists
- `"Cannot send friend request to yourself"` - Self-friend request attempted
- `"Friend request already exists"` - Duplicate request
- `"Cannot send friend request"` - User is blocked
- `"Database error"` - Internal server error
- `"Internal server error"` - Generic server error

### Business Rules
1. Users cannot send friend requests to themselves
2. Users cannot send duplicate friend requests
3. Friend requests can only be sent to active users
4. Blocked users cannot send friend requests to each other
5. Only pending requests can be accepted or rejected
6. Only existing friendships can be removed
7. Pagination limits are enforced (max 50 items per page)
8. All operations require valid authentication tokens

### Rate Limiting
While not explicitly implemented in the current API, consider implementing rate limiting for friend request operations to prevent spam and abuse.