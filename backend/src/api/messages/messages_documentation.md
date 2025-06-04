# Messages API Documentation

## Overview

The Messages API provides endpoints for managing messages within conversations, including deleting messages and marking them as read. This API requires authentication and enforces conversation participation rules to ensure users can only interact with messages they have access to.

**Base URL:** `/api/messages/`  
**Authentication:** Bearer token required  
**Content Type:** `application/json`

## Table of Contents

- [Authentication](#authentication)
- [Endpoints](#endpoints)
  - [DELETE /api/messages/:messageId](#delete-apimessagesmessageid)
  - [PUT /api/messages/:messageId/read](#put-apimessagesmessageidread)
- [Error Handling](#error-handling)
- [Data Models](#data-models)

## Authentication

All endpoints require authentication via Bearer token in the Authorization header:

```
Authorization: Bearer <your-token>
```

The authenticated user must be an active participant in the conversation to access message-related operations.

## Endpoints

### DELETE /api/messages/:messageId

Deletes a message from a conversation. Only the sender of the message can delete their own messages.

**Authentication:** Required (Bearer token)

**Parameters:**

| Parameter | Type | Location | Required | Description |
|-----------|------|----------|----------|-------------|
| messageId | integer | Path/Query | Yes | Unique identifier of the message to delete |

**Path Parameters:**
- **messageId** - The ID of the message to delete (extracted from URL path `/api/messages/123` or query parameter `?messageId=123`)

**Authorization Rules:**
- User must be the sender of the message
- User must still be an active participant in the conversation
- Message must exist and be accessible

**Request Example:**

```bash
# Using cURL
curl -X DELETE \
  "https://api.example.com/api/messages/123" \
  -H "Authorization: Bearer your-token-here" \
  -H "Content-Type: application/json"
```

```javascript
// Using JavaScript fetch
const response = await fetch('/api/messages/123', {
  method: 'DELETE',
  headers: {
    'Authorization': 'Bearer your-token-here',
    'Content-Type': 'application/json'
  }
});

const result = await response.json();
```

**Response:**

**Success (200 OK):**
```json
{
  "success": true,
  "message": "Message deleted successfully",
  "deleted_message_id": 123,
  "conversation_id": 456
}
```

**Error Responses:**
- **400 Bad Request** - Invalid or missing message ID
- **403 Forbidden** - User is not the sender or not a conversation participant
- **404 Not Found** - Message not found
- **405 Method Not Allowed** - Wrong HTTP method used
- **500 Internal Server Error** - Database or server error

### PUT /api/messages/:messageId/read

Marks a message as read by the current user. Users cannot mark their own messages as read.

**Authentication:** Required (Bearer token)

**Parameters:**

| Parameter | Type | Location | Required | Description |
|-----------|------|----------|----------|-------------|
| messageId | integer | Path/Query | Yes | Unique identifier of the message to mark as read |

**Path Parameters:**
- **messageId** - The ID of the message to mark as read (extracted from URL path `/api/messages/123/read` or query parameter `?messageId=123`)

**Authorization Rules:**
- User must be a participant in the conversation
- User cannot mark their own messages as read
- Message must exist and be accessible

**Request Example:**

```bash
# Using cURL
curl -X PUT \
  "https://api.example.com/api/messages/123/read" \
  -H "Authorization: Bearer your-token-here" \
  -H "Content-Type: application/json"
```

```javascript
// Using JavaScript fetch
const response = await fetch('/api/messages/123/read', {
  method: 'PUT',
  headers: {
    'Authorization': 'Bearer your-token-here',
    'Content-Type': 'application/json'
  }
});

const result = await response.json();
```

**Response:**

**Success (200 OK) - Message marked as read:**
```json
{
  "success": true,
  "message": "Message marked as read successfully",
  "message_id": 123,
  "read_at": "2024-01-15T10:30:00Z"
}
```

**Success (200 OK) - Message already read:**
```json
{
  "success": true,
  "message": "Message already marked as read",
  "read_at": "2024-01-15T09:25:00Z"
}
```

**Error Responses:**
- **400 Bad Request** - Invalid message ID or attempting to mark own message as read
- **404 Not Found** - Message not found or access denied
- **405 Method Not Allowed** - Wrong HTTP method used
- **500 Internal Server Error** - Database or server error

## Error Handling

All endpoints follow a consistent error response format:

```json
{
  "success": false,
  "message": "Error description"
}
```

### Common Error Codes

| Status Code | Description |
|-------------|-------------|
| 400 | Bad Request - Invalid parameters or business rule violation |
| 403 | Forbidden - Insufficient permissions for the operation |
| 404 | Not Found - Resource doesn't exist or user lacks access |
| 405 | Method Not Allowed - Incorrect HTTP method |
| 500 | Internal Server Error - Database or server-side error |

### Error Scenarios

**Delete Message Errors:**
- Attempting to delete another user's message
- Message doesn't exist
- User not in conversation
- Database connection issues

**Mark Read Errors:**
- Attempting to mark own message as read
- Message not accessible to user
- Invalid message ID format

## Data Models

### Message Object

```json
{
  "message_id": 123,
  "conversation_id": 456,
  "sender_id": 789,
  "content": "Hello, world!",
  "is_read": true,
  "read_at": "2024-01-15T10:30:00Z",
  "created_at": "2024-01-15T10:25:00Z"
}
```

### Response Objects

**Delete Success Response:**
```json
{
  "success": true,
  "message": "Message deleted successfully",
  "deleted_message_id": 123,
  "conversation_id": 456
}
```

**Mark Read Success Response:**
```json
{
  "success": true,
  "message": "Message marked as read successfully",
  "message_id": 123,
  "read_at": "2024-01-15T10:30:00Z"
}
```

**Error Response:**
```json
{
  "success": false,
  "message": "Error description"
}
```

## Implementation Notes

### URL Parameter Handling
Both endpoints support flexible message ID extraction:
1. **Primary method:** Extract from URL path (e.g., `/api/messages/123`)
2. **Fallback method:** Extract from query parameter (e.g., `?messageId=123`)

### Database Transactions
The delete endpoint uses database transactions to ensure data consistency when:
- Deleting the message
- Updating the conversation's `updated_at` timestamp

### CORS Support
Both endpoints include CORS headers to support cross-origin requests:
- `Access-Control-Allow-Origin: *`
- `Access-Control-Allow-Methods: DELETE, PUT`
- `Access-Control-Allow-Headers: Content-Type, Authorization`

### Business Rules
- **Message Deletion:** Only message senders can delete their own messages
- **Read Status:** Users cannot mark their own messages as read (business logic prevents this)
- **Conversation Participation:** Users must be active participants to perform any message operations
- **Idempotency:** Marking an already-read message as read returns success without error

### Optional Features (Commented Out)
The delete endpoint includes optional time-based restrictions (currently disabled):
- Prevent deletion of messages older than 24 hours
- Can be enabled by uncommenting the relevant code section