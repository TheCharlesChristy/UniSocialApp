<?php

class APIHandler {
    protected $baseURL;
    protected $defaultHeaders;

    public function __construct($baseURL = '/webdev/backend/src/api') {
        $this->baseURL = $baseURL;
        $this->defaultHeaders = [
            'Content-Type' => 'application/json',
        ];
    }

    // Generic cURL wrapper with error handling
    public function request($endpoint, $options = []) {
        $url = $this->baseURL . $endpoint . '.php';
        
        $ch = curl_init();
        
        // Default cURL options
        $curlOptions = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HEADER => true,
            CURLOPT_NOBODY => false
        ];

        // Set headers
        $headers = array_merge($this->defaultHeaders, $options['headers'] ?? []);
        $headerArray = [];
        foreach ($headers as $key => $value) {
            $headerArray[] = "$key: $value";
        }
        $curlOptions[CURLOPT_HTTPHEADER] = $headerArray;

        // Set HTTP method and body
        $method = $options['method'] ?? 'GET';
        switch (strtoupper($method)) {
            case 'POST':
                $curlOptions[CURLOPT_POST] = true;
                if (isset($options['body'])) {
                    $curlOptions[CURLOPT_POSTFIELDS] = $options['body'];
                }
                break;
            case 'PUT':
                $curlOptions[CURLOPT_CUSTOMREQUEST] = 'PUT';
                if (isset($options['body'])) {
                    $curlOptions[CURLOPT_POSTFIELDS] = $options['body'];
                }
                break;
            case 'DELETE':
                $curlOptions[CURLOPT_CUSTOMREQUEST] = 'DELETE';
                break;
            case 'GET':
            default:
                // GET is default
                break;
        }

        curl_setopt_array($ch, $curlOptions);

        try {
            $response = curl_exec($ch);
            
            if ($response === false) {
                throw new Exception('cURL error: ' . curl_error($ch));
            }

            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $responseHeaders = substr($response, 0, $headerSize);
            $responseBody = substr($response, $headerSize);

            curl_close($ch);

            // Parse content type
            $contentType = '';
            if (preg_match('/Content-Type:\s*([^\r\n]+)/i', $responseHeaders, $matches)) {
                $contentType = trim($matches[1]);
            }

            // Parse response data
            $responseData = $responseBody;
            if (strpos($contentType, 'application/json') !== false) {
                $decodedData = json_decode($responseBody, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $responseData = $decodedData;
                }
            }

            // Debug logging (you might want to use error_log instead)
            error_log('API Response: ' . json_encode([
                'url' => $url,
                'status' => $httpCode,
                'contentType' => $contentType,
                'responseData' => $responseData
            ]));

            // Check for HTTP errors
            if ($httpCode >= 400) {
                if (is_array($responseData) && isset($responseData['message'])) {
                    throw new Exception($responseData['message']);
                } elseif (is_string($responseData) && trim($responseData)) {
                    throw new Exception($responseData);
                } else {
                    throw new Exception("HTTP error! status: $httpCode");
                }
            }

            return $responseData;

        } catch (Exception $e) {
            if (isset($ch)) {
                curl_close($ch);
            }
            
            // Enhanced error logging
            error_log('API request failed: ' . json_encode([
                'url' => $url,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]));
            
            throw $e;
        }
    }

    // GET request
    public function get($endpoint, $params = []) {
        $queryString = http_build_query($params);
        $fullEndpoint = $queryString ? "$endpoint?$queryString" : $endpoint;
        
        return $this->request($fullEndpoint, [
            'method' => 'GET'
        ]);
    }

    // POST request
    public function post($endpoint, $data = []) {
        return $this->request($endpoint, [
            'method' => 'POST',
            'body' => json_encode($data)
        ]);
    }

    // PUT request
    public function put($endpoint, $data = []) {
        return $this->request($endpoint, [
            'method' => 'PUT',
            'body' => json_encode($data)
        ]);
    }

    // DELETE request
    public function delete($endpoint) {
        return $this->request($endpoint, [
            'method' => 'DELETE'
        ]);
    }

    // Upload file
    public function upload($endpoint, $formData) {
        $headers = $this->defaultHeaders;
        unset($headers['Content-Type']); // Let cURL set the boundary for multipart data

        return $this->request($endpoint, [
            'method' => 'POST',
            'headers' => $headers,
            'body' => $formData
        ]);
    }

    // Get with authentication token
    public function authenticatedRequest($endpoint, $options = []) {
        $token = $this->getAuthToken();
        if (!$token) {
            throw new Exception('No authentication token found');
        }

        $authHeaders = array_merge([
            'Authorization' => "Bearer $token"
        ], $options['headers'] ?? []);

        $options['headers'] = $authHeaders;
        return $this->request($endpoint, $options);
    }

    // Token management - using PHP sessions instead of localStorage
    public function getAuthToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION['auth_token'] ?? null;
    }

    public function setAuthToken($token, $persistent = false) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['auth_token'] = $token;
        
        // If persistent, you might want to also set a cookie
        if ($persistent) {
            setcookie('auth_token', $token, time() + (86400 * 30), '/'); // 30 days
        }
    }

    public function removeAuthToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        unset($_SESSION['auth_token']);
        
        // Also remove cookie if it exists
        if (isset($_COOKIE['auth_token'])) {
            setcookie('auth_token', '', time() - 3600, '/');
        }
    }

    // Check if user is authenticated
    public function isAuthenticated() {
        return !empty($this->getAuthToken());
    }
}