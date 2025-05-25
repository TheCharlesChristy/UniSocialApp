<?php
/**
 * Database Connection Handler
 * 
 * Establishes secure connection to MySQL/MariaDB database
 * Implements singleton pattern for efficient connection management
 * Automatically initializes database schema when RESET_DB is true
 */

// Define the directory root path
define('DB_ROOT_DIR', dirname(__FILE__));
define('SCHEMA_DIR', dirname(DB_ROOT_DIR) . DIRECTORY_SEPARATOR . 'schema');
define('CONFIG_PATH', DB_ROOT_DIR . DIRECTORY_SEPARATOR . 'config.txt');

// Include configuration and schema installer
require_once DB_ROOT_DIR . DIRECTORY_SEPARATOR . 'config.php';
require_once DB_ROOT_DIR . DIRECTORY_SEPARATOR . 'schema_installer.php';

/**
 * DatabaseHandler class
 * 
 * Handles database connection and operations
 * Implements singleton pattern
 */
class DatabaseHandler {
    private static $instance = null;
    private $pdo = null;
    private $config = null;
    private $connected = false;
    private $errors = [];
    
    /**
     * Private constructor to enforce singleton pattern
     */
    private function __construct() {
        // Initialize configuration
        $this->config = new DatabaseConfig(CONFIG_PATH);
        
        // Check if configuration has errors
        if ($this->config->hasErrors()) {
            $this->errors = $this->config->getErrors();
            $this->logError("Configuration errors: " . implode(', ', $this->errors));
            return;
        }
        
        // Establish database connection
        $this->connect();
        
        // Initialize schema if RESET_DB is true
        if ($this->connected && $this->config->get('RESET_DB', false) === true) {
            $this->initializeSchema();
        }
    }
    
    /**
     * Get singleton instance
     * 
     * @return DatabaseHandler Singleton instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new DatabaseHandler();
        }
        
        return self::$instance;
    }
    
    /**
     * Connect to database
     * 
     * @return bool True if connection successful, false otherwise
     */
    private function connect() {
        try {
            // Get host data (host and port)
            $hostData = $this->config->getHostData();
            
            // Prepare DSN
            $dsn = "mysql:host={$hostData['host']};port={$hostData['port']};charset=utf8mb4";
            
            // Add database name if not creating/resetting
            if (!$this->config->get('RESET_DB', false)) {
                $dsn .= ";dbname=" . $this->config->get('DB_NAME');
            }
            
            // Create PDO connection
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->pdo = new PDO(
                $dsn,
                $this->config->get('DB_USER'),
                $this->config->get('DB_PASS'),
                $options
            );
            
            $this->connected = true;
            $this->log("Database connection established successfully");
            
            return true;
        } catch (PDOException $e) {
            $this->errors[] = "Database connection failed: " . $e->getMessage();
            $this->logError("Database connection failed: " . $e->getMessage());
            $this->connected = false;
            
            return false;
        }
    }
    
    /**
     * Initialize database schema
     * 
     * @return bool True if initialization successful, false otherwise
     */
    private function initializeSchema() {
        try {
            // Create database if it doesn't exist
            $dbName = $this->config->get('DB_NAME');
            $this->pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName`");
            $this->pdo->exec("USE `$dbName`");
            
            // Initialize schema
            $schemaInstaller = new SchemaInstaller($this->pdo, SCHEMA_DIR);
            $result = $schemaInstaller->installSchema();
            
            if (!$result) {
                $this->errors = array_merge($this->errors, $schemaInstaller->getErrors());
                return false;
            }
            
            $this->log("Database schema initialized successfully");
            return true;
        } catch (PDOException $e) {
            $this->errors[] = "Schema initialization failed: " . $e->getMessage();
            $this->logError("Schema initialization failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Execute a query that returns data
     * 
     * @param string $sql SQL query
     * @param array $params Parameters for prepared statement
     * @return array|false Query results or false on failure
     */
    public function query($sql, $params = []) {
        if (!$this->connected) {
            $this->errors[] = "Database not connected";
            return false;
        }
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            $this->errors[] = "Query failed: " . $e->getMessage();
            $this->logError("Query failed: " . $e->getMessage() . " - SQL: $sql");
            return false;
        }
    }
    
    /**
     * Execute a query that does not return data
     * 
     * @param string $sql SQL query
     * @param array $params Parameters for prepared statement
     * @return int|false Number of affected rows or false on failure
     */
    public function execute($sql, $params = []) {
        if (!$this->connected) {
            $this->errors[] = "Database not connected";
            return false;
        }
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            $this->errors[] = "Execution failed: " . $e->getMessage();
            $this->logError("Execution failed: " . $e->getMessage() . " - SQL: $sql");
            return false;
        }
    }
    
    /**
     * Prepare a statement for repeated execution
     * 
     * @param string $sql SQL query
     * @return PDOStatement|false Prepared statement or false on failure
     */
    public function prepare($sql) {
        if (!$this->connected) {
            $this->errors[] = "Database not connected";
            return false;
        }
        
        try {
            return $this->pdo->prepare($sql);
        } catch (PDOException $e) {
            $this->errors[] = "Statement preparation failed: " . $e->getMessage();
            $this->logError("Statement preparation failed: " . $e->getMessage() . " - SQL: $sql");
            return false;
        }
    }
    
    /**
     * Begin a transaction
     * 
     * @return bool True if transaction started, false otherwise
     */
    public function beginTransaction() {
        if (!$this->connected) {
            $this->errors[] = "Database not connected";
            return false;
        }
        
        return $this->pdo->beginTransaction();
    }
    
    /**
     * Commit a transaction
     * 
     * @return bool True if transaction committed, false otherwise
     */
    public function commit() {
        if (!$this->connected) {
            $this->errors[] = "Database not connected";
            return false;
        }
        
        return $this->pdo->commit();
    }
    
    /**
     * Roll back a transaction
     * 
     * @return bool True if transaction rolled back, false otherwise
     */
    public function rollBack() {
        if (!$this->connected) {
            $this->errors[] = "Database not connected";
            return false;
        }
        
        return $this->pdo->rollBack();
    }
      /**
     * Get the last insert ID
     * 
     * @return string|false Last insert ID or false on failure
     */
    public function getLastInsertId() {
        if (!$this->connected) {
            $this->errors[] = "Database not connected";
            return false;
        }
        
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Check if database is connected
     * 
     * @return bool True if connected, false otherwise
     */
    public function isConnected() {
        return $this->connected;
    }
    
    /**
     * Get last error
     * 
     * @return string Last error message
     */
    public function getLastError() {
        return end($this->errors) ?: "";
    }
    
    /**
     * Get all errors
     * 
     * @return array Array of error messages
     */
    public function getErrors() {
        return $this->errors;
    }
    
    /**
     * Log message to file
     * 
     * @param string $message Message to log
     */
    private function log($message) {
        $logFile = DB_ROOT_DIR . DIRECTORY_SEPARATOR . 'db.log';
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] $message" . PHP_EOL;
        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }
    
    /**
     * Log error message to file
     * 
     * @param string $message Error message to log
     */
    private function logError($message) {
        $logFile = DB_ROOT_DIR . DIRECTORY_SEPARATOR . 'db_error.log';
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] ERROR: $message" . PHP_EOL;
        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }
    
    /**
     * Prevent cloning of singleton instance
     */
    private function __clone() {}
    
    /**
     * Prevent unserialization of singleton instance
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

// Create global database instance
$Database = DatabaseHandler::getInstance();

// Return database instance when file is included
return $Database;
