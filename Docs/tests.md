# Tests

## Test Database Connection

Done by going to [test_db_conn](../tests/test_db_conn.php) on the web.

## Test Authentication API Endpoints

All tests are done in the [test_auth](../tests/test_auth_endpoints.php) file.

### Test Registration

In the registration tab of the above.

#### Test Registration with Valid Data

Input test data for registration with valid username, email, and password.

#### Test Registration with existing username

Input test data for registration with an existing username.

#### Test Registration with existing email

Input test data for registration with an existing email.

### Test Login

In the login tab of the above.

#### Test Login with Valid Credentials

Input test data for login with valid username/email and password.

#### Test Login with Invalid Credentials

Input test data for login with invalid username/email and password.

### Test Logout

In the logout tab of the above.

#### Test Logout with Valid Token

Input test data for logout with a valid token.

#### Test Logout with Invalid Token

Input test data for logout with an invalid token.

### Test Forgot Password

Press send request in the forgot password tab of the above.

### Test Reset Password

Do the Test Forgot Password first to get the token. This should be in email_logs in backend/src/api/auth
Then go to the reset password tab of the above and enter the token and new password.

### Test Verify Email

Upon creating an account a token is put into the email_logs.txt file in the backend/src/api/auth directory.
Get the token from there and put it in the verify email tab of the above.

## Test User API Endpoints

All tests are done in the [test_user](../tests/test_user_endpoints.php) file.

### Test Get User Profile

In the get user profile tab of the above. Enter the auth token and select a user id.

#### Test Get User Profile with Valid Token

Input test data for getting user profile with a valid token and user id.

#### Test Get User Profile with Invalid Token

Input test data for getting user profile with an invalid token and user id.

#### Test Get User Profile with Invalid User ID

Input test data for getting user profile with a valid token and an invalid user id.

#### Test Get User Profile with No Token

Input test data for getting user profile with no token and a valid user id.

### Test Update User Profile

In the update user profile tab of the above. Enter the auth token and select a user id.

#### Test Update User Profile with Valid Token

Input test data for updating user profile with a valid token and user id.

### Test Update Password

In the update password tab of the above. Enter the auth token and select a user id.

#### Test Update Password with Valid Token

Input test data for updating password with a valid token and user id.

#### Test Update Password with Incorrect Old Password

Input test data for updating password with a valid token and user id and an incorrect old password.

### Test Get User Posts

In the get user posts tab of the above. Enter the auth token and select a user id.

#### Test Get User Posts with Valid Token

Input test data for getting user posts with a valid token and user id.

#### Test Get User Posts with Invalid Token

Input test data for getting user posts with an invalid token and user id.

#### Test Get User Posts with Invalid User ID

Input test data for getting user posts with a valid token and an invalid user id.

### Test Search Users

In the search users tab of the above. Enter the auth token and select a user id.

#### Test Search Users with Valid Token

Input test data for searching users with a valid token and user id.

#### Test Search Users with Invalid Token

Input test data for searching users with an invalid token and user id.

#### Test Search Users with Existing Username

Input test data for searching users with a valid token and user id and an existing username.

#### Test Search Users with Non-existing Username

Input test data for searching users with a valid token and user id and a non-existing username.

#### Test Search Users with Existing Email

Input test data for searching users with a valid token and user id and an existing email.

#### Test Search Users with Non-existing Email

Input test data for searching users with a valid token and user id and a non-existing email.

### Test User Suggestions

In the user suggestions tab of the above. Enter the auth token and select a user id.

#### Test User Suggestions with Valid Token

Input test data for getting user suggestions with a valid token and user id.

### Test Auth Test

In the auth test tab of the above. Enter the auth token and select a user id.

#### Test Auth Test with Valid Token

Input test data for testing auth with a valid token.

#### Test Auth Test with Invalid Token

Input test data for testing auth with an invalid token.