Posts API Documentation
Overview
The Posts API provides comprehensive functionality for managing social media posts, comments, and engagement features. This RESTful API supports creating text, photo, and video posts, managing comments with nested replies, handling likes, and retrieving personalized feeds with privacy controls.
Base URL: /api/posts/
Authentication: All endpoints require Bearer token authentication
Content Type: application/json
CORS: Enabled for all origins

Table of Contents

Posts Management
Comments Management
Engagement Features
Feed & Discovery
Data Models
Error Handling


Posts Management
Create Post
Creates a new post with support for text, photo, or video content.
Endpoint: POST /api/posts/create_post
Authentication: Required
Content Types Supported:

application/json (for text posts)
multipart/form-data (for media posts)

Parameters:
ParameterTypeRequiredDescriptionpost_typestringYesType of post: text, photo, or videoprivacy_levelstringYesPrivacy setting: public, friends, or privatecaptionstringConditionalRequired for text posts, optional for media posts (max 2000 chars)location_namestringNoLocation name (max 255 chars)location_latfloatNoLatitude (-90 to 90)location_lngfloatNoLongitude (-180 to 180)mediafileConditionalRequired for photo/video posts (max 50MB)
Supported Media Types:

Photos: JPEG, PNG, GIF
Videos: MP4, AVI, MOV

Example Request (Text Post):
json{
  "post_type": "text",
  "privacy_level": "public",
  "caption": "Just had an amazing day at the beach! 🏖️",
  "location_name": "Venice Beach",
  "location_lat": 34.0195,
  "location_lng": -118.4912
}
Example Response:
json{
  "success": true,
  "message": "Post created successfully",
  "post_id": 123
}

Get Specific Post
Retrieves detailed information about a specific post.
Endpoint: GET /api/posts/get_post/{postId} or GET /api/posts/get_post?id={postId}
Authentication: Required
Parameters:
ParameterTypeRequiredDescriptionpostIdintegerYesUnique post identifier
Example Response:
json{
  "success": true,
  "post": {
    "post_id": 123,
    "user_id": 456,
    "username": "johndoe",
    "first_name": "John",
    "last_name": "Doe",
    "profile_picture": "media/profiles/456_avatar.jpg",
    "caption": "Beautiful sunset today!",
    "post_type": "photo",
    "media_url": "media/images/posts/456_1234567890_abc123.jpg",
    "created_at": "2024-01-15T18:30:00Z",
    "updated_at": "2024-01-15T18:30:00Z",
    "privacy_level": "public",
    "location_lat": 34.0195,
    "location_lng": -118.4912,
    "location_name": "Santa Monica",
    "likes_count": 25,
    "comments_count": 8,
    "user_has_liked": 1
  }
}

Update Post
Updates an existing post (caption, privacy, or location only).
Endpoint: PUT /api/posts/update_post or POST /api/posts/update_post
Authentication: Required (owner only)
Parameters:
ParameterTypeRequiredDescriptionpost_idintegerYesPost ID to updatecaptionstringNoNew caption (max 2000 chars, required for text posts)privacy_levelstringNoNew privacy setting: public, friends, or privatelocation_namestringNoNew location namelocation_latfloatNoNew latitudelocation_lngfloatNoNew longitude
Example Request:
json{
  "post_id": 123,
  "caption": "Updated: Beautiful sunset at the pier!",
  "privacy_level": "friends"
}
Response:
json{
  "success": true,
  "message": "Post updated successfully"
}

Delete Post
Permanently deletes a post and associated media.
Endpoint: DELETE /api/posts/delete_post or POST /api/posts/delete_post
Authentication: Required (owner or admin only)
Parameters:
ParameterTypeRequiredDescriptionpost_idintegerYesPost ID to delete
Example Request:
json{
  "post_id": 123
}
Response:
json{
  "success": true,
  "message": "Post deleted successfully"
}

Comments Management
Add Comment
Adds a comment or reply to a post.
Endpoint: POST /api/posts/add_comment
Authentication: Required
Parameters:
ParameterTypeRequiredDescriptionpost_idintegerYesTarget post IDcontentstringYesComment text (max 1000 chars)parent_comment_idintegerNoParent comment ID for replies
Example Request:
json{
  "post_id": 123,
  "content": "Great photo! Love the lighting.",
  "parent_comment_id": null
}
Example Response:
json{
  "success": true,
  "message": "Comment added successfully",
  "comment": {
    "comment_id": 789,
    "post_id": 123,
    "user_id": 456,
    "username": "johndoe",
    "first_name": "John",
    "last_name": "Doe",
    "profile_picture": "media/profiles/456_avatar.jpg",
    "content": "Great photo! Love the lighting.",
    "created_at": "2024-01-15T19:15:00Z",
    "updated_at": "2024-01-15T19:15:00Z",
    "parent_comment_id": null,
    "likes_count": 0,
    "user_has_liked": 0
  }
}

Get Comments
Retrieves comments for a post with nested reply structure.
Endpoint: GET /api/posts/get_comments
Authentication: Required
Parameters:
ParameterTypeRequiredDescriptionpost_idintegerYesPost ID to get comments forpageintegerNoPage number (default: 1)limitintegerNoComments per page (1-50, default: 20)
Example Response:
json{
  "success": true,
  "comments": [
    {
      "comment_id": 789,
      "post_id": 123,
      "user_id": 456,
      "username": "johndoe",
      "first_name": "John",
      "last_name": "Doe",
      "profile_picture": "media/profiles/456_avatar.jpg",
      "content": "Great photo!",
      "created_at": "2024-01-15T19:15:00Z",
      "updated_at": "2024-01-15T19:15:00Z",
      "parent_comment_id": null,
      "likes_count": 5,
      "user_has_liked": 0,
      "replies": [
        {
          "comment_id": 790,
          "content": "Thanks!",
          "parent_comment_id": 789,
          "likes_count": 2,
          "user_has_liked": 1
        }
      ]
    }
  ],
  "total_comments": 8,
  "current_page": 1,
  "total_pages": 1
}

Update Comment
Updates comment content (author only).
Endpoint: PUT /api/posts/update_comment or POST /api/posts/update_comment
Authentication: Required (author only)
Parameters:
ParameterTypeRequiredDescriptioncomment_idintegerYesComment ID to updatecontentstringYesNew comment content (max 1000 chars)
Response:
json{
  "success": true,
  "message": "Comment updated successfully"
}

Delete Comment
Deletes a comment and all its replies.
Endpoint: DELETE /api/posts/delete_comment or POST /api/posts/delete_comment
Authentication: Required (author, post owner, or admin)
Parameters:
ParameterTypeRequiredDescriptioncomment_idintegerYesComment ID to delete
Response:
json{
  "success": true,
  "message": "Comment deleted successfully"
}

Engagement Features
Like Post
Adds a like to a post.
Endpoint: POST /api/posts/like_post
Authentication: Required
Parameters:
ParameterTypeRequiredDescriptionpost_idintegerYesPost ID to like
Response:
json{
  "success": true,
  "message": "Post liked successfully",
  "likes_count": 26
}

Unlike Post
Removes a like from a post.
Endpoint: DELETE /api/posts/unlike_post or POST /api/posts/unlike_post
Authentication: Required
Parameters:
ParameterTypeRequiredDescriptionpost_idintegerYesPost ID to unlike
Response:
json{
  "success": true,
  "message": "Post unliked successfully",
  "likes_count": 25
}

Like Comment
Adds a like to a comment.
Endpoint: POST /api/posts/like_comment
Authentication: Required
Parameters:
ParameterTypeRequiredDescriptioncomment_idintegerYesComment ID to like
Response:
json{
  "success": true,
  "message": "Comment liked successfully",
  "likes_count": 6
}

Unlike Comment
Removes a like from a comment.
Endpoint: DELETE /api/posts/unlike_comment or POST /api/posts/unlike_comment
Authentication: Required
Parameters:
ParameterTypeRequiredDescriptioncomment_idintegerYesComment ID to unlike
Response:
json{
  "success": true,
  "message": "Comment unliked successfully",
  "likes_count": 5
}

Get Post Likes
Retrieves users who liked a specific post.
Endpoint: GET /api/posts/get_post_likes
Authentication: Required
Parameters:
ParameterTypeRequiredDescriptionpost_idintegerYesPost IDpageintegerNoPage number (default: 1)limitintegerNoUsers per page (1-50, default: 20)
Example Response:
json{
  "success": true,
  "likes": [
    {
      "like_id": 456,
      "user_id": 789,
      "username": "janedoe",
      "first_name": "Jane",
      "last_name": "Doe",
      "profile_picture": "media/profiles/789_avatar.jpg",
      "created_at": "2024-01-15T20:00:00Z"
    }
  ],
  "total_likes": 25,
  "current_page": 1,
  "total_pages": 2
}

Get Comment Likes
Retrieves users who liked a specific comment.
Endpoint: GET /api/posts/get_comment_likes
Authentication: Required
Parameters:
ParameterTypeRequiredDescriptioncomment_idintegerYesComment IDpageintegerNoPage number (default: 1)limitintegerNoUsers per page (1-50, default: 20)
Response: Same format as Get Post Likes

Feed & Discovery
Get Feed
Retrieves personalized feed with posts from user and friends.
Endpoint: GET /api/posts/get_feed
Authentication: Required
Parameters:
ParameterTypeRequiredDescriptionpageintegerNoPage number (default: 1)limitintegerNoPosts per page (1-50, default: 10)filterstringNoSearch filter for caption/location
Example Response:
json{
  "success": true,
  "posts": [
    {
      "post_id": 123,
      "user_id": 456,
      "username": "johndoe",
      "first_name": "John",
      "last_name": "Doe",
      "profile_picture": "media/profiles/456_avatar.jpg",
      "caption": "Beautiful sunset!",
      "post_type": "photo",
      "media_url": "media/images/posts/456_1234567890_abc123.jpg",
      "created_at": "2024-01-15T18:30:00Z",
      "updated_at": "2024-01-15T18:30:00Z",
      "privacy_level": "public",
      "location_lat": 34.0195,
      "location_lng": -118.4912,
      "location_name": "Santa Monica",
      "likes_count": 25,
      "comments_count": 8,
      "user_has_liked": 1
    }
  ],
  "total_posts": 156,
  "current_page": 1,
  "total_pages": 16
}

Search Posts
Searches posts by caption and location content.
Endpoint: GET /api/posts/search_posts
Authentication: Required
Parameters:
ParameterTypeRequiredDescriptionqstringYesSearch querypageintegerNoPage number (default: 1)limitintegerNoPosts per page (1-50, default: 10)
Example Response:
json{
  "success": true,
  "posts": [
    {
      "post_id": 123,
      "caption": "Beach day at sunset",
      "relevance_score": 2,
      "likes_count": 25,
      "comments_count": 8,
      "user_has_liked": 0
    }
  ],
  "total_posts": 42,
  "current_page": 1,
  "total_pages": 5,
  "search_query": "sunset"
}

Data Models
Post Object
json{
  "post_id": 123,
  "user_id": 456,
  "username": "johndoe",
  "first_name": "John",
  "last_name": "Doe",
  "profile_picture": "media/profiles/456_avatar.jpg",
  "caption": "Post caption text",
  "post_type": "text|photo|video",
  "media_url": "media/images/posts/filename.jpg",
  "created_at": "2024-01-15T18:30:00Z",
  "updated_at": "2024-01-15T18:30:00Z",
  "privacy_level": "public|friends|private",
  "location_lat": 34.0195,
  "location_lng": -118.4912,
  "location_name": "Location Name",
  "likes_count": 25,
  "comments_count": 8,
  "user_has_liked": 1
}
Comment Object
json{
  "comment_id": 789,
  "post_id": 123,
  "user_id": 456,
  "username": "johndoe",
  "first_name": "John",
  "last_name": "Doe",
  "profile_picture": "media/profiles/456_avatar.jpg",
  "content": "Comment text",
  "created_at": "2024-01-15T19:15:00Z",
  "updated_at": "2024-01-15T19:15:00Z",
  "parent_comment_id": null,
  "likes_count": 5,
  "user_has_liked": 0,
  "replies": []
}
Like Object
json{
  "like_id": 456,
  "user_id": 789,
  "username": "janedoe",
  "first_name": "Jane",
  "last_name": "Doe",
  "profile_picture": "media/profiles/789_avatar.jpg",
  "created_at": "2024-01-15T20:00:00Z"
}

Error Handling
Common HTTP Status Codes

200 OK - Request successful
400 Bad Request - Invalid parameters or validation error
401 Unauthorized - Authentication required or invalid token
403 Forbidden - Access denied (insufficient permissions)
404 Not Found - Resource not found or access denied
405 Method Not Allowed - HTTP method not supported
500 Internal Server Error - Database or server error

Error Response Format
json{
  "success": false,
  "message": "Descriptive error message"
}
Common Error Scenarios
Authentication Errors:

Missing or invalid Bearer token
User account not active

Validation Errors:

Required fields missing
Content length limits exceeded
Invalid file types or sizes
Invalid coordinates or privacy levels

Permission Errors:

Attempting to modify others' content
Accessing private content without permission
Post/comment not found (may indicate privacy restriction)

Business Logic Errors:

Already liked/unliked content
Commenting on inaccessible posts
Replying to non-existent parent comments


Privacy & Access Control
The API implements comprehensive privacy controls:

Public posts: Visible to all authenticated users
Friends posts: Visible only to accepted friends and the author
Private posts: Visible only to the author
Comments: Inherit the privacy level of their parent post
User visibility: Only active accounts are included in responses

Authentication is required for all endpoints, and users can only access content they have permission to view based on friendship status and privacy settings.