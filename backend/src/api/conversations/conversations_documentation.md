# Conversations API Documentation

## Overview
The Conversations API enables users to create, manage, and participate in both private and group conversations. This API provides comprehensive messaging functionality including conversation management, message sending/retrieval, and participant handling.

## Base URL
```
/api/conversations
```

## Authentication
All endpoints require authentication via Bearer token in the Authorization header.

```
Authorization: Bearer <token>
```

## Table of Contents
- [Create Conversation](#create-conversation)
- [Get User Conversations](#get-user-conversations)
- [Get Conversation Details](#get-conversation-details)
- [Get Conversation Messages](#get-conversation-messages)
- [Send Message](#send-message)
- [Leave Conversation](#leave-conversation)
- [Delete Conversation](#delete-conversation)
- [Data Models](#data-models)
- [Error Handling](#error-handling)

---

## Create Conversation

Creates a new conversation between users. Automatically detects if it should be a private chat (2 participants) or group chat (3+ participants).

**Endpoint:** `POST /api/conversations`

**Authentication:** Required

**Request Body:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| participants | array | Yes | Array of user IDs to include in conversation |
| is_group_chat | boolean | No | Force group chat mode (auto-detected if 3+ participants) |
| group_name | string | Conditional | Required for group chats, max 100 characters |

**Request Example:**
```json
{
  "participants": [123, 456, 789],
  "is_group_chat": true,
  "group_name": "Project Team Chat"
}
```

**Response:**
- **200 OK** - Conversation created successfully or already exists
- **400 Bad Request** - Invalid input data
- **500 Internal Server Error** - Server error

**Success Response Example:**
```json
{
  "success": true,
  "message": "Conversation created successfully",
  "conversation_id": 42
}
```

**Existing Conversation Response:**
```json
{
  "success": true,
  "message": "Conversation already exists",
  "conversation_id": 42,
  "existing": true
}
```

---

## Get User Conversations

Retrieves all conversations for the authenticated user with pagination support.

**Endpoint:** `GET /api/conversations`

**Authentication:** Required

**Query Parameters:**
| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| page | integer | No | 1 | Page number for pagination |
| limit | integer | No | 20 | Number of conversations per page (max 100) |

**Example Request:**
```
GET /api/conversations?page=1&limit=10
```

**Response:**
- **200 OK** - Conversations retrieved successfully
- **500 Internal Server Error** - Server error

**Success Response Example:**
```json
{
  "success": true,
  "message": "Conversations retrieved successfully",
  "conversations": [
    {
      "conversation_id": 42,
      "is_group_chat": false,
      "group_name": null,
      "created_at": "2024-01-15T10:30:00Z",
      "updated_at": "2024-01-15T14:22:00Z",
      "last_message_content": "Hey, how are you?",
      "last_message_time": "2024-01-15T14:22:00Z",
      "unread_count": 2,
      "participants": [...],
      "participant_count": 2,
      "display_name": "John Doe",
      "display_picture": "profile.jpg"
    }
  ],
  "total_conversations": 15,
  "current_page": 1,
  "total_pages": 2
}
```

---

## Get Conversation Details

Retrieves detailed information about a specific conversation including participants and statistics.

**Endpoint:** `GET /api/conversations/:conversationId`

**Authentication:** Required

**Path Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| conversationId | integer | Yes | ID of the conversation |

**Alternative Query Parameter:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| conversationId | integer | Yes | ID of the conversation (fallback method) |

**Example Request:**
```
GET /api/conversations/42
```

**Response:**
- **200 OK** - Conversation details retrieved successfully
- **403 Forbidden** - User not authorized to access conversation
- **404 Not Found** - Conversation not found
- **500 Internal Server Error** - Server error

**Success Response Example:**
```json
{
  "success": true,
  "message": "Conversation details retrieved successfully",
  "conversation": {
    "conversation_id": 42,
    "is_group_chat": true,
    "group_name": "Project Team Chat",
    "created_at": "2024-01-15T10:30:00Z",
    "updated_at": "2024-01-15T14:22:00Z",
    "total_messages": 157,
    "last_message_time": "2024-01-15T14:22:00Z",
    "unread_count": 3
  },
  "participants": [...],
  "left_participants": [...]
}
```

---

## Get Conversation Messages

Retrieves messages from a conversation with pagination support.

**Endpoint:** `GET /api/conversations/:conversationId/messages`

**Authentication:** Required

**Path Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| conversationId | integer | Yes | ID of the conversation |

**Query Parameters:**
| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| page | integer | No | 1 | Page number for pagination |
| limit | integer | No | 50 | Number of messages per page (max 100) |
| before_message_id | integer | No | null | Get messages before this message ID |

**Example Request:**
```
GET /api/conversations/42/messages?page=1&limit=20
```

**Response:**
- **200 OK** - Messages retrieved successfully
- **403 Forbidden** - User not authorized to access conversation
- **500 Internal Server Error** - Server error

**Success Response Example:**
```json
{
  "success": true,
  "message": "Messages retrieved successfully",
  "messages": [
    {
      "message_id": 1234,
      "conversation_id": 42,
      "sender_id": 123,
      "content": "Hello everyone!",
      "created_at": "2024-01-15T14:22:00Z",
      "is_read": true,
      "read_at": "2024-01-15T14:25:00Z",
      "sender": {
        "user_id": 123,
        "username": "johndoe",
        "first_name": "John",
        "last_name": "Doe",
        "profile_picture": "profile.jpg"
      },
      "is_own_message": false
    }
  ],
  "total_messages": 157,
  "current_page": 1,
  "total_pages": 8,
  "conversation_id": 42
}
```

---

## Send Message

Sends a new message to a conversation.

**Endpoint:** `POST /api/conversations/:conversationId/messages`

**Authentication:** Required

**Path Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| conversationId | integer | Yes | ID of the conversation |

**Request Body:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| content | string | Yes | Message content (max 5000 characters) |

**Request Example:**
```json
{
  "content": "Hello everyone! How's the project going?"
}
```

**Response:**
- **200 OK** - Message sent successfully
- **400 Bad Request** - Invalid input data
- **403 Forbidden** - User not authorized to send message
- **404 Not Found** - Conversation not found
- **500 Internal Server Error** - Server error

**Success Response Example:**
```json
{
  "success": true,
  "message": "Message sent successfully",
  "message_id": 1235,
  "sent_at": "2024-01-15T14:30:00Z",
  "conversation_id": 42,
  "content": "Hello everyone! How's the project going?"
}
```

---

## Leave Conversation

Allows a user to leave a group conversation. Private conversations cannot be left.

**Endpoint:** `POST /api/conversations/leave_conversation`

**Authentication:** Required

**Request Body:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| conversation_id | integer | Yes | ID of the conversation to leave |

**Request Example:**
```json
{
  "conversation_id": 42
}
```

**Response:**
- **200 OK** - Successfully left conversation
- **400 Bad Request** - Invalid input or cannot leave private conversation
- **404 Not Found** - User not a participant in conversation
- **500 Internal Server Error** - Server error

**Success Response Example:**
```json
{
  "success": true,
  "message": "Successfully left the conversation",
  "data": {
    "conversation_id": 42,
    "left_at": "2024-01-15T15:00:00Z",
    "remaining_participants": 2,
    "ownership_transferred": true,
    "new_owner_id": 456
  }
}
```

---

## Delete Conversation

Permanently deletes a conversation and all its messages. Only conversation owners can delete group conversations.

**Endpoint:** `DELETE /api/conversations/delete_conversation`

**Authentication:** Required

**Request Body:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| conversation_id | integer | Yes | ID of the conversation to delete |

**Request Example:**
```json
{
  "conversation_id": 42
}
```

**Response:**
- **200 OK** - Conversation deleted successfully
- **400 Bad Request** - Invalid conversation ID
- **403 Forbidden** - User not authorized to delete conversation
- **404 Not Found** - User not a participant in conversation
- **500 Internal Server Error** - Server error

**Success Response Example:**
```json
{
  "success": true,
  "message": "Conversation deleted successfully",
  "data": {
    "conversation_id": 42,
    "conversation_type": "group",
    "conversation_name": "Project Team Chat",
    "messages_deleted": 157,
    "deleted_at": "2024-01-15T15:30:00Z"
  }
}
```

---

## Data Models

### Conversation Object
```json
{
  "conversation_id": 42,
  "is_group_chat": true,
  "group_name": "Project Team Chat",
  "created_at": "2024-01-15T10:30:00Z",
  "updated_at": "2024-01-15T14:22:00Z",
  "display_name": "John Doe",
  "display_picture": "profile.jpg"
}
```

### Message Object
```json
{
  "message_id": 1234,
  "conversation_id": 42,
  "sender_id": 123,
  "content": "Hello everyone!",
  "created_at": "2024-01-15T14:22:00Z",
  "is_read": true,
  "read_at": "2024-01-15T14:25:00Z",
  "is_own_message": false
}
```

### Participant Object
```json
{
  "user_id": 123,
  "username": "johndoe",
  "first_name": "John",
  "last_name": "Doe",
  "profile_picture": "profile.jpg",
  "account_status": "active",
  "joined_at": "2024-01-15T10:30:00Z",
  "left_at": null
}
```

---

## Error Handling

### Common Error Codes
- **400 Bad Request** - Invalid input data, missing required fields, or business logic violations
- **401 Unauthorized** - Missing or invalid authentication token
- **403 Forbidden** - User lacks permission to perform the action
- **404 Not Found** - Requested resource doesn't exist or user lacks access
- **405 Method Not Allowed** - Incorrect HTTP method used
- **500 Internal Server Error** - Server-side error

### Error Response Format
```json
{
  "success": false,
  "message": "Error description"
}
```

### Business Rules
1. **Private Conversations**: Automatically created for 2 participants, cannot be left (only deleted)
2. **Group Conversations**: Require 3+ participants and a group name
3. **Ownership**: Group conversation ownership transfers to the next earliest participant when the owner leaves
4. **Message Limits**: Messages cannot exceed 5000 characters
5. **Participant Limits**: Maximum 50 participants per conversation
6. **Access Control**: Users can only access conversations they actively participate in

---

## Notes

- All timestamps are returned in ISO 8601 format (YYYY-MM-DDTHH:MM:SSZ)
- The API uses database transactions to ensure data consistency
- Messages are returned in chronological order (oldest first) for conversation viewing
- Unread message counts are calculated relative to the authenticated user
- Profile pictures and other media references are stored as filenames/paths