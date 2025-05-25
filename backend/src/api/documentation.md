# UniSocialApp API Documentation

## Overview

This document provides comprehensive documentation for the UniSocialApp REST API. The API is built with PHP and MySQL, providing endpoints for user management, posts, comments, messaging, friendships, notifications, and administrative functions.

## Base URL
```
/backend/src/api/
```

## Authentication

Most endpoints require authentication via Bearer token in the Authorization header:
```
Authorization: Bearer YOUR_JWT_TOKEN
```

## Response Format

All API responses follow this standard format:
```json
{
  "success": true|false,
  "message": "Description of the result",
  "data": { ... }
}
```

---

## Authentication Endpoints

### Register User
- **Endpoint**: `POST /auth/register`
- **Purpose**: Create a new user account
- **Authentication**: Not required
- **Request Body**:
```json
{
  "username": "johndoe",
  "email": "john@example.com",
  "password": "SecurePass123",
  "first_name": "John",
  "last_name": "Doe",
  "date_of_birth": "1990-01-01"
}
```
- **Response**:
```json
{
  "success": true,
  "message": "User registered successfully. Check your email to verify your account.",
  "user_id": 123
}
```

### Login
- **Endpoint**: `POST /auth/login`
- **Purpose**: Authenticate user and receive access token
- **Authentication**: Not required
- **Request Body**:
```json
{
  "email": "john@example.com",
  "password": "SecurePass123"
}
```
- **Response**:
```json
{
  "success": true,
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "user_id": 123,
  "expiration": "2025-05-26T10:30:00+00:00"
}
```

### Logout
- **Endpoint**: `POST /auth/logout`
- **Purpose**: Invalidate current session token
- **Authentication**: Required
- **Request Body**:
```json
{
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
}
```
- **Response**:
```json
{
  "success": true,
  "message": "Successfully logged out"
}
```

### Forgot Password
- **Endpoint**: `POST /auth/forgot-password`
- **Purpose**: Request password reset via email
- **Authentication**: Not required
- **Request Body**:
```json
{
  "email": "john@example.com"
}
```
- **Response**:
```json
{
  "success": true,
  "message": "If your email is registered, you will receive password reset instructions"
}
```

### Reset Password
- **Endpoint**: `POST /auth/reset-password`
- **Purpose**: Reset password using token from email
- **Authentication**: Not required
- **Request Body**:
```json
{
  "token": "reset_token_from_email",
  "new_password": "NewSecurePass456"
}
```
- **Response**:
```json
{
  "success": true,
  "message": "Password has been reset successfully"
}
```

### Verify Email
- **Endpoint**: `GET /auth/verify-email/:token`
- **Purpose**: Verify user email using token
- **Authentication**: Not required
- **Response**:
```json
{
  "success": true,
  "message": "Email verified successfully"
}
```

---

## User Management Endpoints

### Get Current User Profile
- **Endpoint**: `GET /users/me`
- **Purpose**: Retrieve authenticated user's profile
- **Authentication**: Required
- **Response**:
```json
{
  "success": true,
  "message": "User profile retrieved successfully",
  "user": {
    "user_id": 123,
    "username": "johndoe",
    "email": "john@example.com",
    "first_name": "John",
    "last_name": "Doe",
    "profile_picture": "media/images/profile/123_1620000000.jpg",
    "bio": "Software developer",
    "date_of_birth": "1990-01-01",
    "registration_date": "2025-01-01 10:00:00",
    "account_status": "active",
    "role": "user"
  }
}
```

### Update Profile
- **Endpoint**: `PUT /users/me`
- **Purpose**: Update user profile information
- **Authentication**: Required
- **Content-Type**: `multipart/form-data` (for file uploads) or `application/json`
- **Request Body**:
```json
{
  "first_name": "John",
  "last_name": "Smith",
  "bio": "Updated bio"
}
```
- **Response**:
```json
{
  "success": true,
  "message": "Profile updated successfully",
  "updated_fields": ["first_name", "last_name", "bio"],
  "profile_picture": "media/images/profile/123_1620000000.jpg"
}
```

### Update Password
- **Endpoint**: `PUT /users/me/password`
- **Purpose**: Change user password
- **Authentication**: Required
- **Request Body**:
```json
{
  "current_password": "OldPassword123",
  "new_password": "NewPassword456"
}
```
- **Response**:
```json
{
  "success": true,
  "message": "Password updated successfully"
}
```

### Get User Profile
- **Endpoint**: `GET /users/:userId`
- **Purpose**: View another user's public profile
- **Authentication**: Required
- **Response**:
```json
{
  "success": true,
  "user": {
    "user_id": 456,
    "username": "janedoe",
    "first_name": "Jane",
    "last_name": "Doe",
    "profile_picture": "media/images/profile/456_1620000000.jpg",
    "bio": "Photographer",
    "friendship_status": "accepted"
  }
}
```

### Search Users
- **Endpoint**: `GET /users/search?query=john&page=1&limit=10`
- **Purpose**: Search for users by name or username
- **Authentication**: Required
- **Response**:
```json
{
  "success": true,
  "users": [
    {
      "user_id": 456,
      "username": "janedoe",
      "first_name": "Jane",
      "last_name": "Doe",
      "profile_picture": "media/images/profile/456_1620000000.jpg",
      "bio": "Photographer",
      "friendship_status": null
    }
  ],
  "total_results": 25,
  "current_page": 1,
  "total_pages": 3
}
```

### Get User Posts
- **Endpoint**: `GET /users/:userId/posts?page=1&limit=10`
- **Purpose**: Get posts from a specific user
- **Authentication**: Required
- **Response**:
```json
{
  "success": true,
  "posts": [
    {
      "post_id": 789,
      "user_id": 456,
      "username": "janedoe",
      "first_name": "Jane",
      "last_name": "Doe",
      "profile_picture": "media/images/profile/456_1620000000.jpg",
      "caption": "Beautiful sunset",
      "post_type": "photo",
      "media_url": "media/images/posts/789_1620000000.jpg",
      "created_at": "2025-05-25 18:30:00",
      "privacy_level": "public",
      "location_name": "Beach",
      "likes_count": 15,
      "comments_count": 3,
      "user_has_liked": false
    }
  ],
  "total_posts": 45,
  "current_page": 1,
  "total_pages": 5
}
```

### Get User Suggestions
- **Endpoint**: `GET /users/suggestions?limit=10`
- **Purpose**: Get suggested users for friendship
- **Authentication**: Required
- **Response**:
```json
{
  "success": true,
  "users": [
    {
      "user_id": 789,
      "username": "bobsmith",
      "first_name": "Bob",
      "last_name": "Smith",
      "profile_picture": "media/images/profile/789_1620000000.jpg",
      "bio": "Tech enthusiast",
      "mutual_friends_count": 5
    }
  ],
  "count": 10
}
```

### Block User
- **Endpoint**: `POST /users/:userId/block`
- **Purpose**: Block another user
- **Authentication**: Required
- **Response**:
```json
{
  "success": true,
  "message": "User blocked successfully"
}
```

### Unblock User
- **Endpoint**: `DELETE /users/:userId/block`
- **Purpose**: Unblock a previously blocked user
- **Authentication**: Required
- **Response**:
```json
{
  "success": true,
  "message": "User unblocked successfully"
}
```

### Get Blocked Users
- **Endpoint**: `GET /users/blocked?page=1&limit=20`
- **Purpose**: Get list of blocked users
- **Authentication**: Required
- **Response**:
```json
{
  "success": true,
  "data": [
    {
      "user_id": 999,
      "username": "blockeduser",
      "full_name": "Blocked User",
      "profile_picture": "media/images/profile/999_1620000000.jpg",
      "blocked_at": "2025-05-20 14:30:00"
    }
  ],
  "pagination": {
    "current_page": 1,
    "total_pages": 1,
    "total_count": 1,
    "per_page": 20
  }
}
```

### Delete User Account
- **Endpoint**: `DELETE /users/delete_user`
- **Purpose**: Delete user's own account or admin delete any account
- **Authentication**: Required
- **Request Body**:
```json
{
  "userId": 123
}
```
- **Response**:
```json
{
  "success": true,
  "message": "User account deleted successfully",
  "deleted_user_id": 123,
  "deleted_username": "johndoe"
}
```

---

## Posts Endpoints

### Get Feed
- **Endpoint**: `GET /posts/get_feed?page=1&limit=10&filter=sunset`
- **Purpose**: Get posts for user's main feed
- **Authentication**: Required
- **Response**:
```json
{
  "success": true,
  "posts": [
    {
      "post_id": 789,
      "user_id": 456,
      "username": "janedoe",
      "first_name": "Jane",
      "last_name": "Doe",
      "profile_picture": "media/images/profile/456_1620000000.jpg",
      "caption": "Beautiful sunset at the beach",
      "post_type": "photo",
      "media_url": "media/images/posts/789_1620000000.jpg",
      "created_at": "2025-05-25 18:30:00",
      "updated_at": "2025-05-25 18:30:00",
      "privacy_level": "public",
      "location_lat": 40.7128,
      "location_lng": -74.0060,
      "location_name": "Manhattan Beach",
      "likes_count": 15,
      "comments_count": 3,
      "user_has_liked": false
    }
  ],
  "total_posts": 50,
  "current_page": 1,
  "total_pages": 5
}
```

### Get Specific Post
- **Endpoint**: `GET /posts/get_post/:postId`
- **Purpose**: Get details of a specific post
- **Authentication**: Required
- **Response**:
```json
{
  "success": true,
  "post": {
    "post_id": 789,
    "user_id": 456,
    "username": "janedoe",
    "first_name": "Jane",
    "last_name": "Doe",
    "profile_picture": "media/images/profile/456_1620000000.jpg",
    "caption": "Beautiful sunset",
    "post_type": "photo",
    "media_url": "media/images/posts/789_1620000000.jpg",
    "created_at": "2025-05-25 18:30:00",
    "privacy_level": "public",
    "location_name": "Beach",
    "likes_count": 15,
    "comments_count": 3,
    "user_has_liked": false
  }
}
```

### Create Post
- **Endpoint**: `POST /posts/create_post`
- **Purpose**: Create a new post
- **Authentication**: Required
- **Content-Type**: `multipart/form-data` (for media uploads)
- **Request Body**:
```json
{
  "caption": "Beautiful sunset at the beach",
  "post_type": "photo",
  "privacy_level": "public",
  "location_lat": 40.7128,
  "location_lng": -74.0060,
  "location_name": "Manhattan Beach"
}
```
- **Response**:
```json
{
  "success": true,
  "message": "Post created successfully",
  "post_id": 789
}
```

### Update Post
- **Endpoint**: `PUT /posts/update_post`
- **Purpose**: Update an existing post
- **Authentication**: Required
- **Request Body**:
```json
{
  "post_id": 789,
  "caption": "Updated caption",
  "privacy_level": "friends",
  "location_name": "Updated location"
}
```
- **Response**:
```json
{
  "success": true,
  "message": "Post updated successfully"
}
```

### Delete Post
- **Endpoint**: `DELETE /posts/delete_post`
- **Purpose**: Delete a post
- **Authentication**: Required
- **Request Body**:
```json
{
  "post_id": 789
}
```
- **Response**:
```json
{
  "success": true,
  "message": "Post deleted successfully"
}
```

### Like Post
- **Endpoint**: `POST /posts/like_post`
- **Purpose**: Like a post
- **Authentication**: Required
- **Request Body**:
```json
{
  "post_id": 789
}
```
- **Response**:
```json
{
  "success": true,
  "message": "Post liked successfully",
  "likes_count": 16
}
```

### Unlike Post
- **Endpoint**: `DELETE /posts/unlike_post`
- **Purpose**: Remove like from a post
- **Authentication**: Required
- **Request Body**:
```json
{
  "post_id": 789
}
```
- **Response**:
```json
{
  "success": true,
  "message": "Post unliked successfully",
  "likes_count": 15
}
```

### Get Post Likes
- **Endpoint**: `GET /posts/get_post_likes?post_id=789&page=1&limit=20`
- **Purpose**: Get list of users who liked a post
- **Authentication**: Required
- **Response**:
```json
{
  "success": true,
  "likes": [
    {
      "like_id": 123,
      "created_at": "2025-05-25 19:00:00",
      "user_id": 456,
      "username": "janedoe",
      "first_name": "Jane",
      "last_name": "Doe",
      "profile_picture": "media/images/profile/456_1620000000.jpg"
    }
  ],
  "total_likes": 15,
  "current_page": 1,
  "total_pages": 1
}
```

### Search Posts
- **Endpoint**: `GET /posts/search_posts?q=sunset&page=1&limit=10`
- **Purpose**: Search posts by caption and location
- **Authentication**: Required
- **Response**:
```json
{
  "success": true,
  "posts": [
    {
      "post_id": 789,
      "user_id": 456,
      "caption": "Beautiful sunset",
      "post_type": "photo",
      "media_url": "media/images/posts/789_1620000000.jpg",
      "created_at": "2025-05-25 18:30:00",
      "privacy_level": "public",
      "location_name": "Beach",
      "likes_count": 15,
      "comments_count": 3,
      "user_has_liked": false
    }
  ],
  "total_posts": 25,
  "current_page": 1,
  "total_pages": 3,
  "search_query": "sunset"
}
```

---

## Comments Endpoints

### Get Comments
- **Endpoint**: `GET /posts/get_comments?post_id=789&page=1&limit=20`
- **Purpose**: Get comments for a specific post
- **Authentication**: Required
- **Response**:
```json
{
  "success": true,
  "comments": [
    {
      "comment_id": 456,
      "post_id": 789,
      "user_id": 123,
      "username": "johndoe",
      "first_name": "John",
      "last_name": "Doe",
      "profile_picture": "media/images/profile/123_1620000000.jpg",
      "content": "Great photo!",
      "created_at": "2025-05-25 19:00:00",
      "updated_at": "2025-05-25 19:00:00",
      "parent_comment_id": null,
      "likes_count": 2,
      "user_has_liked": false,
      "replies": []
    }
  ],
  "total_comments": 5,
  "current_page": 1,
  "total_pages": 1
}
```

### Add Comment
- **Endpoint**: `POST /posts/add_comment`
- **Purpose**: Add a comment to a post
- **Authentication**: Required
- **Request Body**:
```json
{
  "post_id": 789,
  "content": "Great photo!",
  "parent_comment_id": null
}
```
- **Response**:
```json
{
  "success": true,
  "message": "Comment added successfully",
  "comment": {
    "comment_id": 456,
    "post_id": 789,
    "user_id": 123,
    "content": "Great photo!",
    "created_at": "2025-05-25 19:00:00",
    "parent_comment_id": null,
    "username": "johndoe",
    "first_name": "John",
    "last_name": "Doe",
    "profile_picture": "media/images/profile/123_1620000000.jpg",
    "likes_count": 0,
    "user_has_liked": false
  }
}
```

### Update Comment
- **Endpoint**: `PUT /posts/update_comment`
- **Purpose**: Update an existing comment
- **Authentication**: Required
- **Request Body**:
```json
{
  "comment_id": 456,
  "content": "Updated comment text"
}
```
- **Response**:
```json
{
  "success": true,
  "message": "Comment updated successfully"
}
```

### Delete Comment
- **Endpoint**: `DELETE /posts/delete_comment`
- **Purpose**: Delete a comment
- **Authentication**: Required
- **Request Body**:
```json
{
  "comment_id": 456
}
```
- **Response**:
```json
{
  "success": true,
  "message": "Comment deleted successfully"
}
```

### Like Comment
- **Endpoint**: `POST /posts/like_comment`
- **Purpose**: Like a comment
- **Authentication**: Required
- **Request Body**:
```json
{
  "comment_id": 456
}
```
- **Response**:
```json
{
  "success": true,
  "message": "Comment liked successfully",
  "likes_count": 3
}
```

### Unlike Comment
- **Endpoint**: `DELETE /posts/unlike_comment`
- **Purpose**: Remove like from a comment
- **Authentication**: Required
- **Request Body**:
```json
{
  "comment_id": 456
}
```
- **Response**:
```json
{
  "success": true,
  "message": "Comment unliked successfully",
  "likes_count": 2
}
```

### Get Comment Likes
- **Endpoint**: `GET /posts/get_comment_likes?comment_id=456&page=1&limit=20`
- **Purpose**: Get list of users who liked a comment
- **Authentication**: Required
- **Response**:
```json
{
  "success": true,
  "likes": [
    {
      "like_id": 789,
      "created_at": "2025-05-25 19:30:00",
      "user_id": 123,
      "username": "johndoe",
      "first_name": "John",
      "last_name": "Doe",
      "profile_picture": "media/images/profile/123_1620000000.jpg"
    }
  ],
  "total_likes": 2,
  "current_page": 1,
  "total_pages": 1
}
```

---

## Friends Endpoints

### Get Friends
- **Endpoint**: `GET /friends/get_friends?page=1&limit=20`
- **Purpose**: Get user's friends list
- **Authentication**: Required
- **Response**:
```json
{
  "success": true,
  "friends": [
    {
      "user_id": 456,
      "username": "janedoe",
      "first_name": "Jane",
      "last_name": "Doe",
      "profile_picture": "media/images/profile/456_1620000000.jpg",
      "bio": "Photographer",
      "friends_since": "2025-01-15 10:00:00"
    }
  ],
  "total_friends": 25,
  "current_page": 1,
  "total_pages": 2
}
```

### Get Friend Requests
- **Endpoint**: `GET /friends/get_requests?type=received&page=1&limit=20`
- **Purpose**: Get pending friend requests (received or sent)
- **Authentication**: Required
- **Response**:
```json
{
  "success": true,
  "type": "received",
  "requests": [
    {
      "user_id": 789,
      "username": "bobsmith",
      "first_name": "Bob",
      "last_name": "Smith",
      "profile_picture": "media/images/profile/789_1620000000.jpg",
      "bio": "Tech enthusiast",
      "request_date": "2025-05-25 10:00:00"
    }
  ],
  "total_requests": 3,
  "current_page": 1,
  "total_pages": 1
}
```

### Send Friend Request
- **Endpoint**: `POST /friends/send_request`
- **Purpose**: Send a friend request to another user
- **Authentication**: Required
- **Request Body**:
```json
{
  "user_id": 789
}
```
- **Response**:
```json
{
  "success": true,
  "message": "Friend request sent successfully",
  "target_user": {
    "user_id": 789,
    "username": "bobsmith"
  }
}
```

### Accept Friend Request
- **Endpoint**: `PUT /friends/accept_request/:userId`
- **Purpose**: Accept a pending friend request
- **Authentication**: Required
- **Response**:
```json
{
  "success": true,
  "message": "Friend request accepted successfully"
}
```

### Reject Friend Request
- **Endpoint**: `PUT /friends/reject_request/:userId`
- **Purpose**: Reject a pending friend request
- **Authentication**: Required
- **Response**:
```json
{
  "success": true,
  "message": "Friend request rejected successfully"
}
```

### Remove Friend
- **Endpoint**: `DELETE /friends/remove_friend/:userId`
- **Purpose**: Remove a user from friends list
- **Authentication**: Required
- **Response**:
```json
{
  "success": true,
  "message": "Friend removed successfully"
}
```

### Remove Friend Request
- **Endpoint**: `DELETE /friends/remove_friend_request/:userId`
- **Purpose**: Cancel a sent friend request
- **Authentication**: Required
- **Response**:
```json
{
  "success": true,
  "message": "Friend request removed successfully"
}
```

---

## Conversations & Messaging Endpoints

### Get Conversations
- **Endpoint**: `GET /conversations/get_conversations?page=1&limit=20`
- **Purpose**: Get user's conversations list
- **Authentication**: Required
- **Response**:
```json
{
  "success": true,
  "message": "Conversations retrieved successfully",
  "conversations": [
    {
      "conversation_id": 123,
      "is_group_chat": false,
      "group_name": null,
      "created_at": "2025-05-20 10:00:00",
      "updated_at": "2025-05-25 18:00:00",
      "last_message_content": "Hello there!",
      "last_message_time": "2025-05-25 18:00:00",
      "unread_count": 2,
      "participants": [
        {
          "user_id": 456,
          "username": "janedoe",
          "first_name": "Jane",
          "last_name": "Doe",
          "profile_picture": "media/images/profile/456_1620000000.jpg",
          "joined_at": "2025-05-20 10:00:00"
        }
      ],
      "participant_count": 2,
      "display_name": "Jane Doe",
      "display_picture": "media/images/profile/456_1620000000.jpg"
    }
  ],
  "total_conversations": 5,
  "current_page": 1,
  "total_pages": 1
}
```

### Create Conversation
- **Endpoint**: `POST /conversations/create_conversation`
- **Purpose**: Create a new conversation
- **Authentication**: Required
- **Request Body**:
```json
{
  "participants": [456, 789],
  "is_group_chat": false,
  "group_name": null
}
```
- **Response**:
```json
{
  "success": true,
  "message": "Conversation created successfully",
  "conversation_id": 123
}
```

### Get Conversation Details
- **Endpoint**: `GET /conversations/get_conversation_details/:conversationId`
- **Purpose**: Get detailed information about a conversation
- **Authentication**: Required
- **Response**:
```json
{
  "success": true,
  "message": "Conversation details retrieved successfully",
  "conversation": {
    "conversation_id": 123,
    "is_group_chat": false,
    "group_name": null,
    "created_at": "2025-05-20 10:00:00",
    "updated_at": "2025-05-25 18:00:00",
    "total_messages": 15,
    "last_message_time": "2025-05-25 18:00:00",
    "unread_count": 2,
    "display_name": "Jane Doe",
    "display_picture": "media/images/profile/456_1620000000.jpg"
  },
  "participants": [
    {
      "user_id": 456,
      "username": "janedoe",
      "first_name": "Jane",
      "last_name": "Doe",
      "profile_picture": "media/images/profile/456_1620000000.jpg",
      "account_status": "active",
      "joined_at": "2025-05-20 10:00:00",
      "left_at": null
    }
  ],
  "left_participants": []
}
```

### Get Conversation Messages
- **Endpoint**: `GET /conversations/get_conversation_messages/:conversationId?page=1&limit=50`
- **Purpose**: Get messages from a conversation
- **Authentication**: Required
- **Response**:
```json
{
  "success": true,
  "message": "Messages retrieved successfully",
  "messages": [
    {
      "message_id": 789,
      "conversation_id": 123,
      "sender_id": 456,
      "content": "Hello there!",
      "created_at": "2025-05-25 18:00:00",
      "is_read": false,
      "read_at": null,
      "sender": {
        "user_id": 456,
        "username": "janedoe",
        "first_name": "Jane",
        "last_name": "Doe",
        "profile_picture": "media/images/profile/456_1620000000.jpg"
      },
      "is_own_message": false
    }
  ],
  "total_messages": 15,
  "current_page": 1,
  "total_pages": 1,
  "conversation_id": 123
}
```

### Send Message
- **Endpoint**: `POST /conversations/send_message/:conversationId`
- **Purpose**: Send a message in a conversation
- **Authentication**: Required
- **Request Body**:
```json
{
  "content": "Hello there!"
}
```
- **Response**:
```json
{
  "success": true,
  "message": "Message sent successfully",
  "message_id": 789,
  "sent_at": "2025-05-25 18:00:00",
  "conversation_id": 123,
  "content": "Hello there!"
}
```

### Mark Message as Read
- **Endpoint**: `PUT /messages/mark_message_read/:messageId`
- **Purpose**: Mark a message as read
- **Authentication**: Required
- **Response**:
```json
{
  "success": true,
  "message": "Message marked as read successfully",
  "message_id": 789,
  "read_at": "2025-05-25 18:30:00"
}
```

### Delete Message
- **Endpoint**: `DELETE /messages/delete_message/:messageId`
- **Purpose**: Delete a message (sender only)
- **Authentication**: Required
- **Response**:
```json
{
  "success": true,
  "message": "Message deleted successfully",
  "deleted_message_id": 789,
  "conversation_id": 123
}
```

### Leave Conversation
- **Endpoint**: `POST /conversations/leave_conversation`
- **Purpose**: Leave a group conversation
- **Authentication**: Required
- **Request Body**:
```json
{
  "conversation_id": 123
}
```
- **Response**:
```json
{
  "success": true,
  "message": "Successfully left the conversation",
  "data": {
    "conversation_id": 123,
    "left_at": "2025-05-25 20:00:00",
    "remaining_participants": 2,
    "ownership_transferred": false,
    "new_owner_id": null
  }
}
```

### Delete Conversation
- **Endpoint**: `DELETE /conversations/delete_conversation`
- **Purpose**: Delete a conversation (owner only for groups)
- **Authentication**: Required
- **Request Body**:
```json
{
  "conversation_id": 123
}
```
- **Response**:
```json
{
  "success": true,
  "message": "Conversation deleted successfully",
  "data": {
    "conversation_id": 123,
    "conversation_type": "private",
    "conversation_name": null,
    "messages_deleted": 15,
    "deleted_at": "2025-05-25 20:00:00"
  }
}
```

---

## Notifications Endpoints

### Get Notifications
- **Endpoint**: `GET /notifications/get_notifications?page=1&limit=20&filter=unread`
- **Purpose**: Get user's notifications
- **Authentication**: Required
- **Response**:
```json
{
  "success": true,
  "notifications": [
    {
      "notification_id": 456,
      "sender_id": 789,
      "type": "like",
      "related_content_type": "post",
      "related_content_id": 123,
      "created_at": "2025-05-25 18:00:00",
      "is_read": false,
      "read_at": null,
      "sender_username": "bobsmith",
      "sender_first_name": "Bob",
      "sender_last_name": "Smith",
      "sender_profile_picture": "media/images/profile/789_1620000000.jpg",
      "content_preview": "liked your post",
      "sender_name": "Bob Smith"
    }
  ],
  "total_notifications": 10,
  "unread_count": 5,
  "current_page": 1,
  "total_pages": 1
}
```

### Create Notification
- **Endpoint**: `POST /notifications/create_notification`
- **Purpose**: Create a new notification (system use)
- **Authentication**: Required
- **Request Body**:
```json
{
  "recipient_id": 456,
  "type": "like",
  "related_content_type": "post",
  "related_content_id": 123
}
```
- **Response**:
```json
{
  "success": true,
  "message": "Notification created successfully",
  "notification_id": 456
}
```

### Mark Notification as Read
- **Endpoint**: `PUT /notifications/mark_read/:notificationId`
- **Purpose**: Mark a specific notification as read
- **Authentication**: Required
- **Response**:
```json
{
  "success": true,
  "message": "Notification marked as read"
}
```

### Mark All Notifications as Read
- **Endpoint**: `PUT /notifications/mark_all_read`
- **Purpose**: Mark all notifications as read
- **Authentication**: Required
- **Response**:
```json
{
  "success": true,
  "count": 5,
  "message": "Marked 5 notifications as read"
}
```

### Get Unread Count
- **Endpoint**: `GET /notifications/unread_count`
- **Purpose**: Get count of unread notifications
- **Authentication**: Required
- **Response**:
```json
{
  "success": true,
  "count": 5
}
```

### Delete Notification
- **Endpoint**: `DELETE /notifications/delete_notification/:notificationId`
- **Purpose**: Delete a specific notification
- **Authentication**: Required
- **Response**:
```json
{
  "success": true,
  "message": "Notification deleted successfully"
}
```

---

## Reports Endpoints

### Create Report
- **Endpoint**: `POST /reports/create_report`
- **Purpose**: Report a user, post, or comment
- **Authentication**: Required
- **Request Body**:
```json
{
  "reported_id": 456,
  "content_type": "post",
  "content_id": 789,
  "reason": "spam",
  "description": "This post contains spam content"
}
```
- **Response**:
```json
{
  "success": true,
  "report_id": 123,
  "message": "Report created successfully"
}
```

### Get User Reports
- **Endpoint**: `GET /users/:userId/reports?page=1&limit=20&status=pending`
- **Purpose**: Get reports filed by current user
- **Authentication**: Required
- **Response**:
```json
{
  "success": true,
  "reports": [
    {
      "report_id": 123,
      "reported_user": {
        "user_id": 456,
        "username": "spamuser",
        "first_name": "Spam",
        "last_name": "User",
        "profile_picture": "media/images/profile/456_1620000000.jpg"
      },
      "content_type": "post",
      "content_id": 789,
      "reason": "spam",
      "description": "This post contains spam content",
      "status": "pending",
      "created_at": "2025-05-25 18:00:00",
      "reviewed_at": null,
      "admin_notes": null,
      "content_preview": "Check out this amazing..."
    }
  ],
  "total_reports": 3,
  "current_page": 1,
  "total_pages": 1,
  "filters_applied": {
    "content_type": null,
    "reported_user_id": null,
    "reporter_id": null,
    "reason": null,
    "status": "pending"
  }
}
```

---

## Privacy Settings Endpoints

### Get Privacy Settings
- **Endpoint**: `GET /privacy/get_privacy`
- **Purpose**: Get user's privacy settings
- **Authentication**: Required
- **Response**:
```json
{
  "success": true,
  "privacy_settings": {
    "post_default_privacy": "public",
    "profile_visibility": "public",
    "friend_list_visibility": "friends",
    "who_can_send_requests": "everyone",
    "created_at": "2025-01-01 10:00:00",
    "updated_at": "2025-05-25 18:00:00"
  }
}
```

### Update Privacy Settings
- **Endpoint**: `PUT /privacy/update_privacy`
- **Purpose**: Update user's privacy settings
- **Authentication**: Required
- **Request Body**:
```json
{
  "post_default_privacy": "friends",
  "profile_visibility": "public",
  "friend_list_visibility": "private",
  "who_can_send_requests": "friends_of_friends"
}
```
- **Response**:
```json
{
  "success": true,
  "message": "Privacy settings updated successfully",
  "updated_settings": ["post_default_privacy", "friend_list_visibility", "who_can_send_requests"]
}
```

---

## Search Endpoints

### Global Search
- **Endpoint**: `GET /search/index?q=john&type=all&page=1&limit=10`
- **Purpose**: Search across users and posts
- **Authentication**: Required
- **Response**:
```json
{
  "success": true,
  "message": "Search completed successfully",
  "query": "john",
  "type": "all",
  "results": {
    "users": [
      {
        "user_id": 123,
        "username": "johndoe",
        "first_name": "John",
        "last_name": "Doe",
        "profile_picture": "media/images/profile/123_1620000000.jpg",
        "bio": "Software developer",
        "friendship_status": null
      }
    ],
    "posts": [
      {
        "post_id": 789,
        "user_id": 456,
        "caption": "John's birthday party",
        "post_type": "photo",
        "media_url": "media/images/posts/789_1620000000.jpg",
        "created_at": "2025-05-25 18:30:00",
        "privacy_level": "public",
        "likes_count": 15,
        "comments_count": 3,
        "user_has_liked": false
      }
    ]
  },
  "counts": {
    "users": 1,
    "posts": 1
  }
}
```

### Search Users
- **Endpoint**: `GET /search/users?q=john&page=1&limit=10&status=active`
- **Purpose**: Search specifically for users
- **Authentication**: Required
- **Response**:
```json
{
  "success": true,
  "message": "User search completed successfully",
  "query": "john",
  "filters": {
    "status": "active",
    "role": null
  },
  "users": [
    {
      "user_id": 123,
      "username": "johndoe",
      "first_name": "John",
      "last_name": "Doe",
      "profile_picture": "media/images/profile/123_1620000000.jpg",
      "bio": "Software developer",
      "role": "user",
      "account_status": "active",
      "registration_date": "2025-01-01 10:00:00",
      "friendship_status": null,
      "stats": {
        "posts_count": 25,
        "friends_count": 50
      }
    }
  ],
  "pagination": {
    "total_results": 10,
    "current_page": 1,
    "total_pages": 1,
    "per_page": 10
  }
}
```

### Search Posts
- **Endpoint**: `GET /search/posts?q=sunset&page=1&limit=10&sort_by=relevance&post_type=photo&date_from=2025-05-01`
- **Purpose**: Search specifically for posts
- **Authentication**: Required
- **Response**:
```json
{
  "success": true,
  "message": "Post search completed successfully",
  "search_params": {
    "query": "sunset",
    "location": null,
    "author": null,
    "post_type": "photo",
    "privacy": null,
    "date_from": "2025-05-01",
    "date_to": null,
    "sort_by": "relevance"
  },
  "posts": [
    {
      "post_id": 789,
      "user_id": 456,
      "caption": "Beautiful sunset at the beach",
      "post_type": "photo",
      "media_url": "media/images/posts/789_1620000000.jpg",
      "created_at": "2025-05-25 18:30:00",
      "privacy_level": "public",
      "location_name": "Manhattan Beach",
      "username": "janedoe",
      "first_name": "Jane",
      "last_name": "Doe",
      "profile_picture": "media/images/profile/456_1620000000.jpg",
      "likes_count": 15,
      "comments_count": 3,
      "user_has_liked": false
    }
  ],
  "pagination": {
    "total_results": 25,
    "current_page": 1,
    "total_pages": 3,
    "per_page": 10
  }
}
```

---

## Media Upload Endpoint

### Upload Media
- **Endpoint**: `POST /media/upload`
- **Purpose**: Upload media files (images/videos)
- **Authentication**: Required
- **Content-Type**: `multipart/form-data`
- **Request Body**:
```
type: "profile_picture" or "post_media"
file: [uploaded file]
```
- **Response**:
```json
{
  "success": true,
  "message": "File uploaded successfully",
  "file_path": "media/images/posts/123_1620000000_abc123.jpg",
  "file_size": 2048576,
  "file_type": "image/jpeg"
}
```

---

## Admin Endpoints

### Admin Dashboard
- **Endpoint**: `GET /admin/dashboard`
- **Purpose**: Get admin dashboard statistics
- **Authentication**: Required (Admin role)
- **Response**:
```json
{
  "success": true,
  "message": "Dashboard data retrieved successfully",
  "dashboard": {
    "statistics": {
      "total_users": 1250,
      "active_users": 1100,
      "suspended_users": 25,
      "total_posts": 5670,
      "pending_reports": 12,
      "new_users_this_week": 45,
      "new_posts_this_week": 234
    },
    "recent_users": [
      {
        "user_id": 123,
        "username": "newuser",
        "first_name": "New",
        "last_name": "User",
        "registration_date": "2025-05-25 10:00:00",
        "account_status": "active"
      }
    ]
  }
}
```

### Get All Users (Admin)
- **Endpoint**: `GET /admin/users?page=1&limit=20&status=active&search=john`
- **Purpose**: Get all users for admin management
- **Authentication**: Required (Admin role)
- **Response**:
```json
{
  "success": true,
  "message": "Users retrieved successfully",
  "users": [
    {
      "user_id": 123,
      "username": "johndoe",
      "email": "john@example.com",
      "first_name": "John",
      "last_name": "Doe",
      "profile_picture": "media/images/profile/123_1620000000.jpg",
      "bio": "Software developer",
      "date_of_birth": "1990-01-01",
      "registration_date": "2025-01-01 10:00:00",
      "last_login": "2025-05-25 18:00:00",
      "account_status": "active",
      "role": "user",
      "posts_count": 25,
      "reports_count": 0,
      "friends_count": 50
    }
  ],
  "pagination": {
    "current_page": 1,
    "total_pages": 5,
    "total_users": 100,
    "users_per_page": 20
  }
}
```

### Update User (Admin)
- **Endpoint**: `PUT /admin/update_user`
- **Purpose**: Update user information as admin
- **Authentication**: Required (Admin role)
- **Request Body**:
```json
{
  "user_id": 123,
  "first_name": "John",
  "last_name": "Smith",
  "email": "johnsmith@example.com",
  "account_status": "active",
  "role": "user"
}
```
- **Response**:
```json
{
  "success": true,
  "message": "User updated successfully",
  "user": {
    "user_id": 123,
    "username": "johndoe",
    "email": "johnsmith@example.com",
    "first_name": "John",
    "last_name": "Smith",
    "account_status": "active",
    "role": "user"
  }
}
```

### Suspend User (Admin)
- **Endpoint**: `PUT /admin/suspend_user`
- **Purpose**: Suspend a user account
- **Authentication**: Required (Admin role)
- **Request Body**:
```json
{
  "user_id": 123,
  "reason": "Violation of community guidelines"
}
```
- **Response**:
```json
{
  "success": true,
  "message": "User suspended successfully",
  "user": {
    "user_id": 123,
    "username": "johndoe",
    "account_status": "suspended",
    "suspension_reason": "Violation of community guidelines"
  }
}
```

### Activate User (Admin)
- **Endpoint**: `PUT /admin/activate_user`
- **Purpose**: Activate a suspended user account
- **Authentication**: Required (Admin role)
- **Request Body**:
```json
{
  "user_id": 123
}
```
- **Response**:
```json
{
  "success": true,
  "message": "User activated successfully",
  "user": {
    "user_id": 123,
    "username": "johndoe",
    "account_status": "active"
  }
}
```

### Delete User (Admin)
- **Endpoint**: `DELETE /admin/delete_user`
- **Purpose**: Soft delete a user account
- **Authentication**: Required (Admin role)
- **Request Body**:
```json
{
  "user_id": 123
}
```
- **Response**:
```json
{
  "success": true,
  "message": "User deleted successfully",
  "deleted_user": {
    "user_id": 123,
    "username": "johndoe"
  }
}
```

### Get All Reports (Admin)
- **Endpoint**: `GET /admin/reports?page=1&limit=20&status=pending&content_type=post`
- **Purpose**: Get all reports for admin review
- **Authentication**: Required (Admin role)
- **Response**:
```json
{
  "success": true,
  "message": "Reports retrieved successfully",
  "reports": [
    {
      "report_id": 456,
      "reporter_id": 123,
      "reported_id": 789,
      "content_type": "post",
      "content_id": 456,
      "reason": "spam",
      "description": "This post contains spam content",
      "created_at": "2025-05-25 18:00:00",
      "status": "pending",
      "admin_notes": null,
      "reviewed_by": null,
      "reviewed_at": null,
      "reporter_username": "johndoe",
      "reporter_first_name": "John",
      "reporter_last_name": "Doe",
      "reported_username": "spamuser",
      "reported_first_name": "Spam",
      "reported_last_name": "User",
      "content_details": {
        "id": 456,
        "caption": "Check out this amazing deal...",
        "type": "photo",
        "created_at": "2025-05-25 17:00:00"
      }
    }
  ],
  "pagination": {
    "current_page": 1,
    "total_pages": 2,
    "total_reports": 25,
    "reports_per_page": 20
  }
}
```

### Update Report (Admin)
- **Endpoint**: `PUT /admin/update_report`
- **Purpose**: Update report status and add admin notes
- **Authentication**: Required (Admin role)
- **Request Body**:
```json
{
  "report_id": 456,
  "status": "action_taken",
  "admin_notes": "User has been warned and content removed"
}
```
- **Response**:
```json
{
  "success": true,
  "message": "Report updated successfully",
  "report": {
    "report_id": 456,
    "status": "action_taken",
    "admin_notes": "User has been warned and content removed",
    "reviewed_by": 1,
    "reviewed_at": "2025-05-25 20:00:00"
  }
}
```

### User Analytics (Admin)
- **Endpoint**: `GET /admin/analytics_users?start_date=2025-05-01&end_date=2025-05-25`
- **Purpose**: Get user analytics and statistics
- **Authentication**: Required (Admin role)
- **Response**:
```json
{
  "success": true,
  "message": "User analytics retrieved successfully",
  "analytics": {
    "date_range": {
      "start_date": "2025-05-01",
      "end_date": "2025-05-25"
    },
    "overview": {
      "new_users_in_period": 45,
      "engagement": {
        "total_active_users": 1100,
        "users_with_posts": 800,
        "users_with_likes": 950,
        "users_with_comments": 700,
        "users_with_friends": 900
      }
    },
    "users_by_status": [
      {"account_status": "active", "count": 1100},
      {"account_status": "suspended", "count": 25}
    ],
    "daily_registrations": [
      {"date": "2025-05-01", "count": 5},
      {"date": "2025-05-02", "count": 8}
    ],
    "most_active_users": [
      {
        "user_id": 123,
        "username": "johndoe",
        "first_name": "John",
        "last_name": "Doe",
        "posts_count": 45
      }
    ]
  }
}
```

### Post Analytics (Admin)
- **Endpoint**: `GET /admin/analytics_posts?start_date=2025-05-01&end_date=2025-05-25`
- **Purpose**: Get post analytics and statistics
- **Authentication**: Required (Admin role)
- **Response**:
```json
{
  "success": true,
  "message": "Post analytics retrieved successfully",
  "analytics": {
    "date_range": {
      "start_date": "2025-05-01",
      "end_date": "2025-05-25"
    },
    "overview": {
      "total_posts": 234,
      "engagement": {
        "total_posts": 234,
        "total_likes": 1250,
        "total_comments": 890,
        "avg_likes_per_post": 5.34,
        "avg_comments_per_post": 3.8
      }
    },
    "posts_by_type": [
      {"post_type": "photo", "count": 150},
      {"post_type": "text", "count": 60},
      {"post_type": "video", "count": 24}
    ],
    "daily_posts": [
      {"date": "2025-05-01", "count": 12},
      {"date": "2025-05-02", "count": 15}
    ],
    "top_liked_posts": [
      {
        "post_id": 789,
        "caption": "Beautiful sunset",
        "post_type": "photo",
        "created_at": "2025-05-25 18:30:00",
        "username": "janedoe",
        "likes_count": 45
      }
    ]
  }
}
```

---

## Error Responses

All endpoints can return the following error responses:

### Authentication Errors
```json
{
  "success": false,
  "message": "Authentication required"
}
```

### Authorization Errors
```json
{
  "success": false,
  "message": "Access denied"
}
```

### Validation Errors
```json
{
  "success": false,
  "message": "Email is required"
}
```

### Not Found Errors
```json
{
  "success": false,
  "message": "User not found"
}
```

### Server Errors
```json
{
  "success": false,
  "message": "Internal server error"
}
```

---

## Status Codes

- **200 OK**: Request successful
- **201 Created**: Resource created successfully
- **400 Bad Request**: Invalid request data
- **401 Unauthorized**: Authentication required or failed
- **403 Forbidden**: Access denied
- **404 Not Found**: Resource not found
- **405 Method Not Allowed**: HTTP method not supported
- **409 Conflict**: Resource already exists
- **413 Payload Too Large**: File too large
- **500 Internal Server Error**: Server error

---

## Rate Limiting

The API implements basic rate limiting on certain endpoints:
- File uploads: Limited by file size and user session
- Authentication endpoints: Protected against brute force attempts
- Search endpoints: Pagination limits to prevent overload

---

## File Upload Restrictions

### Profile Pictures
- **Max Size**: 5MB
- **Allowed Types**: JPEG, PNG, GIF
- **Directory**: `media/images/profile/`

### Post Media
- **Max Size**: 50MB
- **Allowed Types**: JPEG, PNG, GIF, MP4, AVI, MOV
- **Directory**: `media/images/posts/`

---

## Notes

1. All timestamps are in MySQL datetime format: `YYYY-MM-DD HH:MM:SS`
2. Privacy levels for posts: `public`, `friends`, `private`
3. User roles: `user`, `admin`
4. Account statuses: `active`, `suspended`, `deleted`
5. Friend request statuses: `pending`, `accepted`
6. Report statuses: `pending`, `reviewed`, `action_taken`, `dismissed`
7. Notification types: `like`, `comment`, `friend_request`, `friend_accept`, `mention`, `tag`

This API follows RESTful principles and provides comprehensive functionality for a social media platform with proper authentication, authorization, and data validation.