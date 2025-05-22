<?php
/**
 * Authentication Utilities
 * 
 * Contains common functions for authentication operations including:
 * - Token generation and validation
 * - Password hashing and verification
 * - Email token generation
 */

class AuthUtils {
    // Token expiration times (in seconds)
    const ACCESS_TOKEN_EXPIRY = 86400; // 24 hours
    const RESET_TOKEN_EXPIRY = 3600; // 1 hour
    const VERIFICATION_TOKEN_EXPIRY = 172800; // 48 hours
    
    // Secret key for JWT tokens - should be moved to a secure config in production
    private static $secretKey = 'your-256-bit-secret-key'; // Change this in production!
    
    /**
     * Generate JWT token
     * 
     * @param int $userId User ID
     * @param string $type Token type (auth, reset, verify)
     * @param int $expiry Expiration time in seconds
     * @return string Generated JWT token
     */
    public static function generateToken($userId, $type = 'auth', $expiry = null) {
        if ($expiry === null) {
            switch ($type) {
                case 'reset':
                    $expiry = self::RESET_TOKEN_EXPIRY;
                    break;
                case 'verify':
                    $expiry = self::VERIFICATION_TOKEN_EXPIRY;
                    break;
                default:
                    $expiry = self::ACCESS_TOKEN_EXPIRY;
            }
        }
        
        $issuedAt = time();
        $expirationTime = $issuedAt + $expiry;
        
        $payload = [
            'iat' => $issuedAt,
            'exp' => $expirationTime,
            'user_id' => $userId,
            'type' => $type,
            'jti' => bin2hex(random_bytes(16)) // Token ID for revocation purposes
        ];
        
        $header = self::base64UrlEncode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payload = self::base64UrlEncode(json_encode($payload));
        $signature = self::base64UrlEncode(hash_hmac('sha256', "$header.$payload", self::$secretKey, true));
        
        return "$header.$payload.$signature";
    }
    
    /**
     * Validate JWT token
     * 
     * @param string $token JWT token
     * @param string $type Expected token type
     * @return array|false Decoded payload or false if invalid
     */
    public static function validateToken($token, $type = 'auth') {
        // Split token
        $tokenParts = explode('.', $token);
        if (count($tokenParts) != 3) {
            return false;
        }
        
        list($header, $payload, $signature) = $tokenParts;
        
        // Verify signature
        $expectedSignature = self::base64UrlEncode(hash_hmac('sha256', "$header.$payload", self::$secretKey, true));
        if (!hash_equals($expectedSignature, $signature)) {
            return false;
        }
        
        // Decode payload
        $decodedPayload = json_decode(self::base64UrlDecode($payload), true);
        if ($decodedPayload === null) {
            return false;
        }
        
        // Check expiration
        if (isset($decodedPayload['exp']) && $decodedPayload['exp'] < time()) {
            return false;
        }
        
        // Check token type
        if (isset($decodedPayload['type']) && $decodedPayload['type'] !== $type) {
            return false;
        }
        
        return $decodedPayload;
    }
    
    /**
     * Base64Url encode
     * 
     * @param string $data Data to encode
     * @return string Base64Url encoded string
     */
    private static function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    /**
     * Base64Url decode
     * 
     * @param string $data Data to decode
     * @return string Decoded data
     */
    private static function base64UrlDecode($data) {
        return base64_decode(strtr($data, '-_', '+/'));
    }
    
    /**
     * Hash password
     * 
     * @param string $password Plain text password
     * @return string Hashed password
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }
    
    /**
     * Verify password
     * 
     * @param string $password Plain text password
     * @param string $hash Hashed password
     * @return bool True if password is valid
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Save token to blacklist (for logout functionality)
     * 
     * @param string $token JWT token to blacklist
     * @param object $db Database connection
     * @return bool True if token was blacklisted
     */
    public static function blacklistToken($token, $db) {
        $tokenParts = explode('.', $token);
        if (count($tokenParts) != 3) {
            return false;
        }
        
        $payload = json_decode(self::base64UrlDecode($tokenParts[1]), true);
        if (!isset($payload['jti']) || !isset($payload['exp'])) {
            return false;
        }
        
        $sql = "INSERT INTO token_blacklist (token_id, expiration) VALUES (?, ?)";
        $result = $db->execute($sql, [$payload['jti'], date('Y-m-d H:i:s', $payload['exp'])]);
        
        return $result !== false;
    }
    
    /**
     * Check if token is blacklisted
     * 
     * @param string $token JWT token
     * @param object $db Database connection
     * @return bool True if token is blacklisted
     */
    public static function isTokenBlacklisted($token, $db) {
        $tokenParts = explode('.', $token);
        if (count($tokenParts) != 3) {
            return true; // Invalid tokens are considered blacklisted
        }
        
        $payload = json_decode(self::base64UrlDecode($tokenParts[1]), true);
        if (!isset($payload['jti'])) {
            return true;
        }
        
        $sql = "SELECT COUNT(*) as count FROM token_blacklist WHERE token_id = ?";
        $result = $db->query($sql, [$payload['jti']]);
        
        if ($result === false || !isset($result[0]['count'])) {
            return true; // If error, consider token blacklisted for security
        }
        
        return (int)$result[0]['count'] > 0;
    }
    
    /**
     * Clean expired tokens from blacklist
     * 
     * @param object $db Database connection
     * @return bool True if cleanup succeeded
     */
    public static function cleanupBlacklist($db) {
        $sql = "DELETE FROM token_blacklist WHERE expiration < NOW()";
        $result = $db->execute($sql);
        
        return $result !== false;
    }
    
    /**
     * Send password reset email
     * 
     * @param string $email User email
     * @param string $token Reset token
     * @return bool True if email was sent
     */
    public static function sendPasswordResetEmail($email, $token) {
        // In a real application, implement proper email sending
        // For now, just log the token for development purposes
        file_put_contents(
            dirname(__FILE__) . '/email_logs.txt',
            date('Y-m-d H:i:s') . " - Password Reset - Email: $email, Token: $token\n",
            FILE_APPEND
        );
        
        return true;
    }
    
    /**
     * Send verification email
     * 
     * @param string $email User email
     * @param string $token Verification token
     * @return bool True if email was sent
     */
    public static function sendVerificationEmail($email, $token) {
        // In a real application, implement proper email sending
        // For now, just log the token for development purposes
        file_put_contents(
            dirname(__FILE__) . '/email_logs.txt',
            date('Y-m-d H:i:s') . " - Email Verification - Email: $email, Token: $token\n",
            FILE_APPEND
        );
        
        return true;
    }
}
