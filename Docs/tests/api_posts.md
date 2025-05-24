
# Test Posts and Media API Endpoints

All tests are done in the [test_posts_media](../tests/test_posts_media.php) file.

## Prerequisites

Before testing posts and media endpoints:

1. **Authentication Required**: Get a valid JWT token by logging in through the auth endpoints
2. **Database Setup**: Ensure posts, likes, comments, and users tables are properly initialized
3. **Media Directory**: Verify `backend/media/images/posts/` directory exists with write permissions

---

## Test Posts Management

### Test Get Feed

In the get feed tab of the above. Enter the auth token.

#### Test Get Feed with Valid Token

Input test data for getting feed posts with a valid token. Test pagination with different page and limit values (1-50).

#### Test Get Feed with Invalid Token

Input test data for getting feed posts with an invalid or expired token.

#### Test Get Feed with No Token

Input test data for getting feed posts without providing an authentication token.

#### Test Get Feed Pagination

Input test data for getting feed posts with valid token and test various page numbers and limits to verify pagination works correctly.

### Test Get Specific Post

In the get specific post tab of the above. Enter the auth token and post ID.

#### Test Get Post with Valid Token and Post ID

Input test data for getting a specific post with a valid token and existing post ID.

#### Test Get Post with Invalid Token

Input test data for getting a specific post with an invalid token and valid post ID.

#### Test Get Post with Invalid Post ID

Input test data for getting a specific post with a valid token and non-existing post ID.

#### Test Get Post Privacy Filtering

Input test data for getting a specific post that belongs to another user with different privacy levels (public, friends, private).

### Test Create Post

In the create post tab of the above. Enter the auth token, content, and privacy setting.

#### Test Create Post with Valid Data

Input test data for creating a post with valid token, content, and privacy setting.

#### Test Create Post with Empty Content

Input test data for creating a post with valid token but empty content.

#### Test Create Post with Invalid Privacy Setting

Input test data for creating a post with valid token, content, but invalid privacy setting.

#### Test Create Post with No Token

Input test data for creating a post without authentication token.

#### Test Create Post with Long Content

Input test data for creating a post with content exceeding the maximum character limit (2000 characters).

### Test Update Post

In the update post tab of the above. Enter the auth token, post ID, new content, and privacy setting.

#### Test Update Post with Valid Data

Input test data for updating a post with valid token, post ID, content, and privacy setting.

#### Test Update Post Not Owned by User

Input test data for updating a post that belongs to another user.

#### Test Update Post with Invalid Post ID

Input test data for updating a post with valid token but non-existing post ID.

#### Test Update Post with Empty Content

Input test data for updating a post with valid token but empty content.

#### Test Update Post Privacy Only

Input test data for updating only the privacy setting of a post without changing content.

### Test Delete Post

In the delete post tab of the above. Enter the auth token and post ID.

#### Test Delete Post with Valid Data

Input test data for deleting a post with valid token and post ID owned by the user.

#### Test Delete Post Not Owned by User

Input test data for deleting a post that belongs to another user.

#### Test Delete Post with Invalid Post ID

Input test data for deleting a post with valid token but non-existing post ID.

#### Test Delete Post with No Token

Input test data for deleting a post without authentication token.

### Test Search Posts

In the search posts tab of the above. Enter the auth token, search query, and page number.

#### Test Search Posts with Valid Query

Input test data for searching posts with valid token and search terms that exist in post content.

#### Test Search Posts with No Results

Input test data for searching posts with valid token and search terms that don't match any posts.

#### Test Search Posts with Empty Query

Input test data for searching posts with valid token but empty search query.

#### Test Search Posts with Invalid Token

Input test data for searching posts with invalid token and valid search query.

#### Test Search Posts Pagination

Input test data for searching posts with valid token and test pagination through multiple pages of results.

---

## Test Likes Management

### Test Like Post

In the like post tab of the above. Enter the auth token and post ID.

#### Test Like Post with Valid Data

Input test data for liking a post with valid token and existing post ID.

#### Test Like Post Already Liked

Input test data for liking a post that the user has already liked.

#### Test Like Post with Invalid Post ID

Input test data for liking a post with valid token but non-existing post ID.

#### Test Like Post with No Token

Input test data for liking a post without authentication token.

#### Test Like Private Post from Non-Friend

Input test data for liking a private post from a user who is not a friend.

### Test Unlike Post

In the unlike post tab of the above. Enter the auth token and post ID.

#### Test Unlike Post with Valid Data

Input test data for unliking a post with valid token and post ID that was previously liked.

#### Test Unlike Post Not Previously Liked

Input test data for unliking a post that the user hasn't liked before.

#### Test Unlike Post with Invalid Post ID

Input test data for unliking a post with valid token but non-existing post ID.

#### Test Unlike Post with No Token

Input test data for unliking a post without authentication token.

### Test Get Post Likes

In the get post likes tab of the above. Enter the auth token, post ID, and page number.

#### Test Get Post Likes with Valid Data

Input test data for getting likes of a post with valid token and existing post ID.

#### Test Get Post Likes with Invalid Post ID

Input test data for getting likes of a post with valid token but non-existing post ID.

#### Test Get Post Likes with No Token

Input test data for getting likes of a post without authentication token.

#### Test Get Post Likes Pagination

Input test data for getting likes of a post with valid token and test pagination through multiple pages.

#### Test Get Post Likes Privacy Filtering

Input test data for getting likes of a private post to verify proper privacy filtering.

---

## Test Comments Management

### Test Get Comments

In the get comments tab of the above. Enter the auth token, post ID, and page number.

#### Test Get Comments with Valid Data

Input test data for getting comments of a post with valid token and existing post ID.

#### Test Get Comments with Invalid Post ID

Input test data for getting comments of a post with valid token but non-existing post ID.

#### Test Get Comments with No Token

Input test data for getting comments of a post without authentication token.

#### Test Get Comments Pagination

Input test data for getting comments of a post with valid token and test pagination through multiple pages.

#### Test Get Comments with Nested Replies

Input test data for getting comments of a post that has nested reply structures to verify proper comment threading.

### Test Add Comment

In the add comment tab of the above. Enter the auth token, post ID, comment content, and optional parent comment ID.

#### Test Add Comment with Valid Data

Input test data for adding a comment to a post with valid token, post ID, and comment content.

#### Test Add Comment Reply

Input test data for adding a reply to an existing comment with valid token, post ID, content, and parent comment ID.

#### Test Add Comment with Empty Content

Input test data for adding a comment with valid token but empty content.

#### Test Add Comment with Invalid Post ID

Input test data for adding a comment with valid token but non-existing post ID.

#### Test Add Comment with No Token

Input test data for adding a comment without authentication token.

#### Test Add Comment to Private Post

Input test data for adding a comment to a private post from a non-friend user.

#### Test Add Comment with Long Content

Input test data for adding a comment with content exceeding the maximum character limit.

### Test Update Comment

In the update comment tab of the above. Enter the auth token, comment ID, and new content.

#### Test Update Comment with Valid Data

Input test data for updating a comment with valid token, comment ID, and new content.

#### Test Update Comment Not Owned by User

Input test data for updating a comment that belongs to another user.

#### Test Update Comment with Invalid Comment ID

Input test data for updating a comment with valid token but non-existing comment ID.

#### Test Update Comment with Empty Content

Input test data for updating a comment with valid token but empty content.

#### Test Update Comment with No Token

Input test data for updating a comment without authentication token.

### Test Delete Comment

In the delete comment tab of the above. Enter the auth token and comment ID.

#### Test Delete Comment with Valid Data

Input test data for deleting a comment with valid token and comment ID owned by the user.

#### Test Delete Comment Not Owned by User

Input test data for deleting a comment that belongs to another user.

#### Test Delete Comment with Invalid Comment ID

Input test data for deleting a comment with valid token but non-existing comment ID.

#### Test Delete Comment with No Token

Input test data for deleting a comment without authentication token.

#### Test Delete Parent Comment with Replies

Input test data for deleting a parent comment that has replies to verify proper handling of nested comments.

---

## Test Media Upload

### Test Upload Image

In the media upload tab of the above. Enter the auth token, select an image file, and choose upload type.

#### Test Upload Image with Valid Data

Input test data for uploading an image with valid token, supported image file (JPEG, PNG, GIF), and valid upload type.

#### Test Upload Image with Unsupported Format

Input test data for uploading a file with unsupported format (e.g., .txt, .exe).

#### Test Upload Image Exceeding Size Limit

Input test data for uploading an image that exceeds the maximum file size limit.

#### Test Upload Image with No Token

Input test data for uploading an image without authentication token.

#### Test Upload Image with Invalid Upload Type

Input test data for uploading an image with an invalid upload type parameter.

#### Test Upload Multiple Images

Input test data for uploading multiple images in sequence to test file naming and storage.

#### Test Upload Image with Special Characters in Filename

Input test data for uploading an image with special characters, spaces, or unicode characters in the filename.

---

## Test Error Handling and Edge Cases

### Test Rate Limiting

Test making multiple rapid requests to verify rate limiting is properly implemented.

### Test Large Payload Handling

Test sending requests with very large JSON payloads to verify proper handling.

### Test Malformed JSON

Test sending malformed JSON data to endpoints that expect JSON input.

### Test SQL Injection Prevention

Test sending potentially malicious input to verify SQL injection prevention.

### Test XSS Prevention

Test adding posts/comments with potentially malicious scripts to verify XSS prevention.

### Test Concurrent Operations

Test simultaneous likes/unlikes, comments, and post operations to verify data integrity.

---

## Expected Response Formats

### Successful Responses

All successful responses should follow this format:

```json
{
  "success": true,
  "message": "Operation completed successfully",
  "data": { /* relevant data */ }
}
```

### Error Responses

All error responses should follow this format:

```json
{
  "success": false,
  "message": "Error description",
  "error_code": "SPECIFIC_ERROR_CODE" // if applicable
}
```

### Pagination Responses

Responses with pagination should include:

```json
{
  "success": true,
  "data": [ /* array of items */ ],
  "total_items": 100,
  "current_page": 1,
  "total_pages": 10,
  "items_per_page": 10
}
```

---

## Testing Checklist

- [ ] All endpoints return proper HTTP status codes
- [ ] All endpoints handle authentication correctly
- [ ] Privacy filtering works correctly for posts and comments
- [ ] Pagination works correctly across all endpoints
- [ ] File uploads handle security validation properly
- [ ] Database constraints are properly enforced
- [ ] Error messages are informative but don't expose sensitive information
- [ ] All input validation works correctly
- [ ] Performance is acceptable under normal load
- [ ] Cross-origin requests are handled properly

#### Test Delete Comment Not Owned by User
Input test data for deleting a comment that belongs to another user.

#### Test Delete Comment with Invalid Comment ID
Input test data for deleting a comment with valid token but non-existing comment ID.

#### Test Delete Comment with No Token
Input test data for deleting a comment without authentication token.

#### Test Delete Parent Comment with Replies
Input test data for deleting a parent comment that has replies to verify proper handling of nested comments.

---

## Test Media Upload

### Test Upload Image

In the media upload tab of the above. Enter the auth token, select an image file, and choose upload type.

#### Test Upload Image with Valid Data
Input test data for uploading an image with valid token, supported image file (JPEG, PNG, GIF), and valid upload type.

#### Test Upload Image with Unsupported Format
Input test data for uploading a file with unsupported format (e.g., .txt, .exe).

#### Test Upload Image Exceeding Size Limit
Input test data for uploading an image that exceeds the maximum file size limit.

#### Test Upload Image with No Token
Input test data for uploading an image without authentication token.

#### Test Upload Image with Invalid Upload Type
Input test data for uploading an image with an invalid upload type parameter.

#### Test Upload Multiple Images
Input test data for uploading multiple images in sequence to test file naming and storage.

#### Test Upload Image with Special Characters in Filename
Input test data for uploading an image with special characters, spaces, or unicode characters in the filename.

---

## Test Error Handling and Edge Cases

### Test Rate Limiting
Test making multiple rapid requests to verify rate limiting is properly implemented.

### Test Large Payload Handling
Test sending requests with very large JSON payloads to verify proper handling.

### Test Malformed JSON
Test sending malformed JSON data to endpoints that expect JSON input.

### Test SQL Injection Prevention
Test sending potentially malicious input to verify SQL injection prevention.

### Test XSS Prevention
Test adding posts/comments with potentially malicious scripts to verify XSS prevention.

### Test Concurrent Operations
Test simultaneous likes/unlikes, comments, and post operations to verify data integrity.

---

## Expected Response Formats

### Successful Responses
All successful responses should follow this format:
```json
{
  "success": true,
  "message": "Operation completed successfully",
  "data": { /* relevant data */ }
}
```

### Error Responses
All error responses should follow this format:
```json
{
  "success": false,
  "message": "Error description",
  "error_code": "SPECIFIC_ERROR_CODE" // if applicable
}
```

### Pagination Responses
Responses with pagination should include:
```json
{
  "success": true,
  "data": [ /* array of items */ ],
  "total_items": 100,
  "current_page": 1,
  "total_pages": 10,
  "items_per_page": 10
}
```

---

## Testing Checklist

- [ ] All endpoints return proper HTTP status codes
- [ ] All endpoints handle authentication correctly
- [ ] Privacy filtering works correctly for posts and comments
- [ ] Pagination works correctly across all endpoints
- [ ] File uploads handle security validation properly
- [ ] Database constraints are properly enforced
- [ ] Error messages are informative but don't expose sensitive information
- [ ] All input validation works correctly
- [ ] Performance is acceptable under normal load
- [ ] Cross-origin requests are handled properly