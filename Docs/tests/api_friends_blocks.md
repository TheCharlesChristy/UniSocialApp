# Friends and Blocking API Test Plan

## Overview
This test plan covers all friend management and user blocking endpoints available in the `test_friends_endpoints.php` test page.

## Prerequisites
- Two user accounts created and verified
- Valid auth tokens for both users
- Access to `http://localhost/tests/test_friends_endpoints.php`

## Test Data Setup
For this test plan, we'll assume:
- **User A**: Your primary test account (Token A)
- **User B**: Secondary test account (Token B, User ID: 7)

---

## Test Execution Steps

### Phase 1: Initial Setup and Authentication

#### Step 1.1: Access Test Page
1. Open browser and navigate to `http://localhost/tests/test_friends_endpoints.php`
2. Verify the page loads with three tabs: "Friends", "Friend Requests", "Blocking"

#### Step 1.2: Authentication Setup
1. Enter **Token A** (your primary user's token) in the "Access Token" field
2. Keep this token ready for switching between users during tests

---

### Phase 2: Friend Request Management

#### Step 2.1: Test Send Friend Request
**Tab:** Friend Requests
**Section:** Send Friend Request

1. **Input:** Target User ID = `7` (User B)
2. **Action:** Click "Send Request"
3. **Expected Success Result:**
   ```json
   {
     "success": true,
     "message": "Friend request sent successfully"
   }
   ```
4. **Expected Error Cases to Test:**
   - Invalid user ID (999): Should return user not found error
   - Sending to yourself: Should return validation error
   - Duplicate request: Should return already sent error

#### Step 2.2: Test Get Outgoing Requests
**Tab:** Friend Requests
**Section:** Get Outgoing Friend Requests

1. **Input:** Page = `1`, Limit = `20`
2. **Action:** Click "Get Outgoing Requests"
3. **Expected Success Result:**
   ```json
   {
     "success": true,
     "data": {
       "requests": [
         {
           "friendship_id": 1,
           "recipient": {
             "user_id": 7,
             "username": "user_b_username",
             "email": "userb@example.com",
             "first_name": "User",
             "last_name": "B",
             "profile_picture": null,
             "bio": null
           },
           "created_at": "2025-05-25 ...",
           "status": "pending"
         }
       ],
       "pagination": {
         "current_page": 1,
         "total_pages": 1,
         "total_requests": 1,
         "per_page": 20
       }
     }
   }
   ```

#### Step 2.3: Switch to User B and Test Incoming Requests
1. **Change Token:** Replace auth token with **Token B**
2. **Tab:** Friend Requests
3. **Section:** Get Friend Requests
4. **Input:** Page = `1`, Limit = `20`
5. **Action:** Click "Get Requests"
6. **Expected Result:** Should show the friend request from User A

#### Step 2.4: Test Accept Friend Request (User B)
**Tab:** Friend Requests
**Section:** Accept Friend Request

1. **Input:** User ID = User A's ID
2. **Action:** Click "Accept Request"
3. **Expected Success Result:**
   ```json
   {
     "success": true,
     "message": "Friend request accepted successfully"
   }
   ```

---

### Phase 3: Friends Management

#### Step 3.1: Test Get Friends List (User B)
**Tab:** Friends
**Section:** Get Friends List

1. **Input:** Page = `1`, Limit = `20`
2. **Action:** Click "Get Friends"
3. **Expected Result:** Should show User A in friends list with status "accepted"

#### Step 3.2: Switch Back to User A and Verify Friendship
1. **Change Token:** Replace with **Token A**
2. **Tab:** Friends
3. **Action:** Click "Get Friends"
4. **Expected Result:** Should show User B in friends list

#### Step 3.3: Test Remove Friend (User A)
**Tab:** Friends
**Section:** Remove Friend

1. **Input:** Friend User ID = `7` (User B)
2. **Action:** Click "Remove Friend"
3. **Confirm:** Click "OK" on confirmation dialog
4. **Expected Success Result:**
   ```json
   {
     "success": true,
     "message": "Friend removed successfully"
   }
   ```

---

### Phase 4: Friend Request Rejection Flow

#### Step 4.1: Re-send Friend Request (User A)
1. **Tab:** Friend Requests
2. **Action:** Send new friend request to User B (repeat Step 2.1)

#### Step 4.2: Test Reject Request (User B)
1. **Switch Token:** Use **Token B**
2. **Tab:** Friend Requests
3. **Section:** Reject Friend Request
4. **Input:** User ID = User A's ID
5. **Action:** Click "Reject Request"
6. **Expected Success Result:**
   ```json
   {
     "success": true,
     "message": "Friend request rejected successfully"
   }
   ```

#### Step 4.3: Test Remove/Cancel Outgoing Request (User A)
1. **Switch Token:** Use **Token A**
2. **Send new request:** Send another friend request to User B
3. **Tab:** Friend Requests
4. **Section:** Remove Friend Request (Cancel Sent Request)
5. **Input:** User ID = `7`
6. **Action:** Click "Remove Request"
7. **Confirm:** Click "OK" on confirmation dialog
8. **Expected Success Result:**
   ```json
   {
     "success": true,
     "message": "Friend request cancelled successfully"
   }
   ```

---

### Phase 5: Blocking Functionality

#### Step 5.1: Test Block User (User A)
**Tab:** Blocking
**Section:** Block User

1. **Input:** User ID to Block = `7` (User B)
2. **Action:** Click "Block User"
3. **Confirm:** Click "OK" on confirmation dialog
4. **Expected Success Result:**
   ```json
   {
     "success": true,
     "message": "User blocked successfully"
   }
   ```

#### Step 5.2: Test Get Blocked Users (User A)
**Tab:** Blocking
**Section:** Get Blocked Users

1. **Input:** Page = `1`, Limit = `20`
2. **Action:** Click "Get Blocked Users"
3. **Expected Result:** Should show User B in blocked users list

#### Step 5.3: Test Friend Request While Blocked
1. **Tab:** Friend Requests
2. **Try to send request:** Attempt to send friend request to User B
3. **Expected Error:** Should return error indicating user is blocked or cannot send request

#### Step 5.4: Test Unblock User (User A)
**Tab:** Blocking
**Section:** Unblock User

1. **Input:** User ID to Unblock = `7`
2. **Action:** Click "Unblock User"
3. **Expected Success Result:**
   ```json
   {
     "success": true,
     "message": "User unblocked successfully"
   }
   ```

#### Step 5.5: Verify Unblock Success
1. **Tab:** Blocking
2. **Action:** Click "Get Blocked Users"
3. **Expected Result:** List should be empty or not contain User B

---

### Phase 6: Error Handling Tests

#### Step 6.1: Test Invalid Authentication
1. **Change Token:** Enter invalid/expired token
2. **Try any action:** Attempt any API call
3. **Expected Error:**
   ```json
   {
     "success": false,
     "message": "Invalid or expired token",
     "http_status": 401
   }
   ```

#### Step 6.2: Test Invalid User IDs
1. **Various endpoints:** Try with User ID = `99999`
2. **Expected Error:**
   ```json
   {
     "success": false,
     "message": "User not found",
     "http_status": 404
   }
   ```

#### Step 6.3: Test Pagination Limits
1. **Any paginated endpoint:** Try with Limit = `999`
2. **Expected Result:** Should be capped at maximum limit (50)

#### Step 6.4: Test Self-Actions
1. **Try to friend yourself:** Use your own user ID
2. **Try to block yourself:** Use your own user ID
3. **Expected Error:** Should return validation errors

---

### Phase 7: Pagination Testing

#### Step 7.1: Test Pagination Parameters
For each paginated endpoint:
1. **Test Page 2:** Set Page = `2`, verify response
2. **Test Different Limits:** Try Limit = `1`, `5`, `10`
3. **Verify Pagination Info:** Check `current_page`, `total_pages`, etc.

---

## Success Criteria

### All Tests Pass When:
- Friend requests can be sent, received, accepted, and rejected
- Outgoing requests are properly listed and can be cancelled
- Friends can be added and removed successfully  
- Users can be blocked and unblocked
- Blocked users list is accurate
- Proper error messages for invalid operations
- Authentication is enforced on all endpoints
- Pagination works correctly
- All JSON responses are properly formatted and displayed

### Error Scenarios Work Correctly:
- Invalid user IDs return 404 errors
- Unauthorized access returns 401 errors
- Duplicate operations return appropriate errors
- Self-targeting operations are prevented
- Invalid tokens are rejected

---

## Test Results Template

**Date:** ___________  
**Tester:** ___________  
**Test Environment:** localhost/webdev

| Test Phase | Test Case | Status | Notes |
|------------|-----------|--------|-------|
| Phase 2.1 | Send Friend Request | PASS/FAIL | |
| Phase 2.2 | Get Outgoing Requests | PASS/FAIL | |
| Phase 2.3 | Get Incoming Requests | PASS/FAIL | |
| Phase 2.4 | Accept Friend Request | PASS/FAIL | |
| Phase 3.1 | Get Friends List | PASS/FAIL | |
| Phase 3.3 | Remove Friend | PASS/FAIL | |
| Phase 4.2 | Reject Request | PASS/FAIL | |
| Phase 4.3 | Cancel Outgoing Request | PASS/FAIL | |
| Phase 5.1 | Block User | PASS/FAIL | |
| Phase 5.2 | Get Blocked Users | PASS/FAIL | |
| Phase 5.4 | Unblock User | PASS/FAIL | |
| Phase 6.x | Error Handling | PASS/FAIL | |
| Phase 7.x | Pagination | PASS/FAIL | |

**Overall Result:** PASS / FAIL

**Additional Notes:**
_________________________________
_________________________________
_________________________________
