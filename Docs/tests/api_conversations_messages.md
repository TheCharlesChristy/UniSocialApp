# Test Conversations and Messages API Endpoints

All tests are done in the [test_messaging_endpoints](../tests/test_messaging_endpoints.php) file.

## Prerequisites

Before testing conversations and messages endpoints:

1. **Authentication Required**: Get a valid JWT token by logging in through the auth endpoints
2. **Database Setup**: Ensure conversations, messages, conversation_participants, and users tables are properly initialized
3. **User Data**: Have at least 2-3 test users available for testing group conversations and message exchanges
4. **Friend Relationships**: Ensure test users have appropriate friend relationships for testing private conversations

---

## Test Conversations Management

### Test Get Conversations

In the get conversations tab of the above. Enter the auth token.

#### Test Get Conversations with Valid Token

Input test data for getting user conversations with a valid token. Test pagination with different page and limit values (1-50).

#### Test Get Conversations with Invalid Token

Input test data for getting conversations with an invalid or expired token.

#### Test Get Conversations with No Token

Input test data for getting conversations without providing an authentication token.

#### Test Get Conversations Pagination

Input test data for getting conversations with valid token and test various page numbers and limits to verify pagination works correctly.

#### Test Get Conversations Empty State

Input test data for getting conversations for a user who has no conversations to verify proper empty state handling.

### Test Create Conversation

In the create conversation tab of the above. Enter the auth token, participant IDs, conversation type, and optional title.

#### Test Create Private Conversation with Valid Data

Input test data for creating a private conversation between two users with valid token and participant ID.

#### Test Create Group Conversation with Valid Data

Input test data for creating a group conversation with valid token, multiple participant IDs, and conversation title.

#### Test Create Conversation with Self

Input test data for creating a conversation where the user includes themselves in the participant list.

#### Test Create Conversation with Invalid Participant ID

Input test data for creating a conversation with valid token but non-existing participant ID.

#### Test Create Conversation with No Token

Input test data for creating a conversation without authentication token.

#### Test Create Duplicate Private Conversation

Input test data for creating a private conversation that already exists between the same participants.

#### Test Create Group Conversation with Single Participant

Input test data for creating a group conversation with only one participant besides the creator.

#### Test Create Conversation with Empty Participants

Input test data for creating a conversation without specifying any participants.

#### Test Create Group Conversation Without Title

Input test data for creating a group conversation without providing a required title.

### Test Get Conversation Details

In the get conversation details tab of the above. Enter the auth token and conversation ID.

#### Test Get Conversation Details with Valid Data

Input test data for getting conversation details with valid token and conversation ID where user is a participant.

#### Test Get Conversation Details with Invalid Token

Input test data for getting conversation details with an invalid token and valid conversation ID.

#### Test Get Conversation Details with Invalid Conversation ID

Input test data for getting conversation details with valid token but non-existing conversation ID.

#### Test Get Conversation Details Unauthorized Access

Input test data for getting conversation details for a conversation where the user is not a participant.

#### Test Get Conversation Details with No Token

Input test data for getting conversation details without authentication token.

### Test Leave Conversation

In the leave conversation tab of the above. Enter the auth token and conversation ID.

#### Test Leave Conversation with Valid Data

Input test data for leaving a group conversation with valid token and conversation ID where user is a participant.

#### Test Leave Private Conversation

Input test data for attempting to leave a private conversation to verify that it's properly rejected.

#### Test Leave Conversation with Invalid Token

Input test data for leaving a conversation with an invalid token and valid conversation ID.

#### Test Leave Conversation with Invalid Conversation ID

Input test data for leaving a conversation with valid token but non-existing conversation ID.

#### Test Leave Conversation Unauthorized Access

Input test data for leaving a conversation where the user is not a participant.

#### Test Leave Conversation Already Left

Input test data for attempting to leave a conversation that the user has already left.

#### Test Leave Conversation with No Token

Input test data for leaving a conversation without authentication token.

#### Test Leave Last Participant in Group

Input test data for leaving a group conversation as the last remaining participant to verify proper handling.

### Test Delete Conversation

In the delete conversation tab of the above. Enter the auth token and conversation ID.

#### Test Delete Conversation with Valid Data (Creator)

Input test data for deleting a conversation with valid token and conversation ID where user is the creator of a group conversation.

#### Test Delete Private Conversation

Input test data for deleting a private conversation with valid token and conversation ID where user is a participant.

#### Test Delete Conversation with Invalid Token

Input test data for deleting a conversation with an invalid token and valid conversation ID.

#### Test Delete Conversation with Invalid Conversation ID

Input test data for deleting a conversation with valid token but non-existing conversation ID.

#### Test Delete Conversation Unauthorized Access (Not Creator)

Input test data for deleting a group conversation where the user is not the creator to verify proper permission enforcement.

#### Test Delete Conversation Unauthorized Access (Not Participant)

Input test data for deleting a conversation where the user is not a participant.

#### Test Delete Conversation with No Token

Input test data for deleting a conversation without authentication token.

#### Test Delete Conversation with Messages

Input test data for deleting a conversation that contains multiple messages to verify cascade deletion works properly.

---

## Test Messages Management

### Test Get Conversation Messages

In the get conversation messages tab of the above. Enter the auth token, conversation ID, and optional pagination parameters.

#### Test Get Messages with Valid Data

Input test data for getting messages from a conversation with valid token and conversation ID where user is a participant.

#### Test Get Messages with Invalid Token

Input test data for getting messages with an invalid token and valid conversation ID.

#### Test Get Messages with Invalid Conversation ID

Input test data for getting messages with valid token but non-existing conversation ID.

#### Test Get Messages Unauthorized Access

Input test data for getting messages from a conversation where the user is not a participant.

#### Test Get Messages Pagination

Input test data for getting messages with valid token and test various page numbers and limits to verify pagination works correctly.

#### Test Get Messages Empty Conversation

Input test data for getting messages from a conversation that has no messages yet.

#### Test Get Messages with No Token

Input test data for getting messages without authentication token.

### Test Send Message

In the send message tab of the above. Enter the auth token, conversation ID, and message content.

#### Test Send Message with Valid Data

Input test data for sending a message to a conversation with valid token, conversation ID, and message content.

#### Test Send Message with Empty Content

Input test data for sending a message with valid token but empty message content.

#### Test Send Message with Invalid Token

Input test data for sending a message with an invalid token and valid conversation ID.

#### Test Send Message with Invalid Conversation ID

Input test data for sending a message with valid token but non-existing conversation ID.

#### Test Send Message Unauthorized Access

Input test data for sending a message to a conversation where the user is not a participant.

#### Test Send Message with Long Content

Input test data for sending a message with content exceeding the maximum character limit (if applicable).

#### Test Send Message with Special Characters

Input test data for sending a message containing special characters, emojis, and unicode text.

#### Test Send Message with No Token

Input test data for sending a message without authentication token.

#### Test Send Message to Update Last Activity

Input test data for sending a message and verify that conversation's last_activity timestamp is updated correctly.

### Test Mark Message as Read

In the mark message as read tab of the above. Enter the auth token and message ID.

#### Test Mark Message Read with Valid Data

Input test data for marking a message as read with valid token and message ID where user is a participant in the conversation.

#### Test Mark Message Read Already Read

Input test data for marking a message as read that has already been marked as read by the user.

#### Test Mark Message Read with Invalid Token

Input test data for marking a message as read with an invalid token and valid message ID.

#### Test Mark Message Read with Invalid Message ID

Input test data for marking a message as read with valid token but non-existing message ID.

#### Test Mark Message Read Unauthorized Access

Input test data for marking a message as read from a conversation where the user is not a participant.

#### Test Mark Message Read Own Message

Input test data for marking the user's own message as read to verify proper handling.

#### Test Mark Message Read with No Token

Input test data for marking a message as read without authentication token.

### Test Delete Message

In the delete message tab of the above. Enter the auth token and message ID.

#### Test Delete Message with Valid Data

Input test data for deleting a message with valid token and message ID owned by the user.

#### Test Delete Message Not Owned by User

Input test data for deleting a message that belongs to another user.

#### Test Delete Message with Invalid Token

Input test data for deleting a message with an invalid token and valid message ID.

#### Test Delete Message with Invalid Message ID

Input test data for deleting a message with valid token but non-existing message ID.

#### Test Delete Message from Unauthorized Conversation

Input test data for deleting a message from a conversation where the user is not a participant.

#### Test Delete Message with No Token

Input test data for deleting a message without authentication token.

#### Test Delete Already Deleted Message

Input test data for attempting to delete a message that has already been deleted.

---

## Test Real-time Features and Data Integrity

### Test Conversation Participant Management

#### Test Adding Participants to Group Conversation

Test adding new participants to an existing group conversation (if implemented).

#### Test Removing Participants from Group Conversation

Test removing participants from an existing group conversation (if implemented).

#### Test Participant Access Validation

Test that only conversation participants can access conversation details and messages.

### Test Message Ordering and Timestamps

#### Test Message Chronological Order

Test that messages are returned in correct chronological order when fetching conversation messages.

#### Test Message Timestamp Accuracy

Test that message timestamps are accurately recorded and displayed.

#### Test Conversation Last Activity Update

Test that conversation's last_activity is properly updated when new messages are sent.

### Test Read Status Tracking

#### Test Read Status Initialization

Test that new messages are properly initialized with unread status for all participants except sender.

#### Test Read Status Updates

Test that marking messages as read properly updates the read status for the specific user.

#### Test Read Status Privacy

Test that users can only see their own read status and cannot access other users' read statuses.

---

## Test Error Handling and Edge Cases

### Test Rate Limiting

Test making multiple rapid requests to verify rate limiting is properly implemented.

### Test Large Message Content

Test sending messages with very large content to verify proper handling and character limits.

### Test Malformed JSON

Test sending malformed JSON data to endpoints that expect JSON input.

### Test SQL Injection Prevention

Test sending potentially malicious input to verify SQL injection prevention.

### Test XSS Prevention

Test sending messages with potentially malicious scripts to verify XSS prevention.

### Test Concurrent Message Operations

Test simultaneous message sending, reading, and deleting to verify data integrity.

### Test Database Transaction Integrity

Test scenarios that could cause database inconsistencies to verify proper transaction handling.

---

## Test Security and Privacy

### Test Authentication Bypass Attempts

#### Test Direct Endpoint Access

Test accessing endpoints directly without authentication headers.

#### Test Token Manipulation

Test using modified or corrupted JWT tokens.

#### Test Expired Token Handling

Test using expired JWT tokens and verify proper rejection.

### Test Authorization Validation

#### Test Cross-User Data Access

Test attempting to access conversations and messages belonging to other users.

#### Test Participant Validation

Test that only conversation participants can perform operations on conversations and messages.

### Test Input Sanitization

#### Test Message Content Sanitization

Test that message content is properly sanitized to prevent XSS and other injection attacks.

#### Test Parameter Validation

Test that all input parameters are properly validated and sanitized.

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
  "pagination": {
    "current_page": 1,
    "total_pages": 10,
    "total_items": 100,
    "items_per_page": 10
  }
}
```

### Conversation Response Format

```json
{
  "success": true,
  "data": {
    "conversation_id": 1,
    "type": "private|group",
    "title": "Conversation Title", // for group conversations
    "created_at": "2024-01-01 12:00:00",
    "last_activity": "2024-01-01 14:30:00",
    "participants": [
      {
        "user_id": 1,
        "username": "user1",
        "profile_picture": "path/to/picture.jpg"
      }
    ]
  }
}
```

### Message Response Format

```json
{
  "success": true,
  "data": {
    "message_id": 1,
    "conversation_id": 1,
    "sender_id": 1,
    "sender_username": "user1",
    "content": "Message content",
    "sent_at": "2024-01-01 12:00:00",
    "is_read": false
  }
}
```

---

## Testing Checklist

- [ ] All endpoints return proper HTTP status codes
- [ ] All endpoints handle authentication correctly
- [ ] Authorization validation prevents unauthorized access
- [ ] Pagination works correctly across all endpoints
- [ ] Database constraints are properly enforced
- [ ] Error messages are informative but don't expose sensitive information
- [ ] All input validation works correctly
- [ ] Message content is properly sanitized
- [ ] Read status tracking works correctly
- [ ] Conversation participant validation is enforced
- [ ] Message ordering is chronologically correct
- [ ] Conversation last_activity updates properly
- [ ] Duplicate conversation prevention works
- [ ] Group vs private conversation handling is correct
- [ ] Leave conversation functionality works correctly
- [ ] Leave conversation prevents private conversation exits
- [ ] Leave conversation validates participant membership
- [ ] Performance is acceptable under normal load
- [ ] Cross-origin requests are handled properly
- [ ] Transaction integrity is maintained
- [ ] Real-time features work as expected

---

## Performance Testing

### Test Large Conversations

Test performance with conversations containing hundreds of messages to verify pagination and query optimization.

### Test Multiple Concurrent Users

Test system behavior with multiple users sending messages simultaneously.

### Test Database Query Optimization

Monitor database query performance, especially for:
- Getting conversations with participant details
- Fetching paginated messages
- Updating read status for multiple messages
- Searching through message content (if implemented)

### Test Memory Usage

Monitor memory usage during intensive messaging operations to ensure no memory leaks.

---

## Integration Testing

### Test with Authentication System

Verify seamless integration with the existing authentication middleware and JWT token validation.

### Test with User Management

Verify integration with user profiles and friend relationships for participant validation.

### Test with Database Constraints

Verify that foreign key constraints and database relationships work correctly across all operations.

### Test Cross-Browser Compatibility

Test the messaging interface across different browsers to ensure compatibility.
