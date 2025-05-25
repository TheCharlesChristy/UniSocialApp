<?php
/**
 * Database Schema Installer
 * 
 * Handles the automatic installation of database schema
 * Executes SQL files in the correct order
 * Provides rollback mechanism for failed installations
 */

class SchemaInstaller {
    private $pdo;
    private $schemaDir;
    private $logFile;
    private $errors = [];
    private $executedFiles = [];
    
    /**
     * Constructor for SchemaInstaller
     * 
     * @param PDO $pdo PDO database connection
     * @param string $schemaDir Directory containing schema files
     * @param string $logFile Path to log file (optional)
     */
    public function __construct($pdo, $schemaDir, $logFile = null) {
        $this->pdo = $pdo;
        $this->schemaDir = rtrim($schemaDir, '/\\') . DIRECTORY_SEPARATOR;
        $this->logFile = $logFile ?: dirname(__FILE__) . DIRECTORY_SEPARATOR . 'schema_install.log';
    }
    
    /**
     * Install all schema files in the correct order
     * 
     * @return bool True if installation successful, false otherwise
     */
    public function installSchema() {        // Define the order of schema files
        $schemaFiles = [
            'table_users.sql',
            'table_posts.sql',
            'table_comments.sql',
            'table_likes.sql',
            'table_friendships.sql',
            'table_blocks.sql',
            'table_reports.sql',
            'table_conversations.sql',
            'table_conversation_participants.sql',
            'table_messages.sql',
            'table_notifications.sql',
            'table_privacy_settings.sql',
            'table_token_blacklist.sql'
        ];
        
        // Check if schema files exist
        foreach ($schemaFiles as $file) {
            $filePath = $this->schemaDir . $file;
            if (!file_exists($filePath)) {
                $this->errors[] = "Schema file not found: $filePath";
                $this->logError("Schema file not found: $filePath");
                return false;
            }
        }
        
        // Start transaction for rollback capability
        $this->pdo->beginTransaction();
        
        try {
            // Process each schema file in order
            foreach ($schemaFiles as $file) {
                $filePath = $this->schemaDir . $file;
                
                // Read SQL file
                $sql = file_get_contents($filePath);
                if ($sql === false) {
                    throw new Exception("Failed to read schema file: $filePath");
                }
                
                // Check if tables already exist
                if ($this->tableExists($file)) {
                    $this->log("Table in $file already exists. Skipping...");
                    continue;
                }
                
                // Execute SQL commands
                $this->log("Executing schema file: $file");
                $result = $this->pdo->exec($sql);
                
                if ($result === false) {
                    $error = $this->pdo->errorInfo();
                    throw new Exception("Failed to execute schema file $file: " . $error[2]);
                }
                
                $this->executedFiles[] = $file;
                $this->log("Successfully executed schema file: $file");
            }
            
            // Commit transaction if all files executed successfully
            $this->pdo->commit();
            $this->log("Database schema installed successfully");
            return true;
        } catch (Exception $e) {
            // Rollback transaction if any file failed
            $this->pdo->rollBack();
            $this->errors[] = $e->getMessage();
            $this->logError("Schema installation failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if a table already exists
     * 
     * @param string $file Schema file name
     * @return bool True if table exists, false otherwise
     */
    private function tableExists($file) {
        // Extract table name from file name (table_xxx.sql -> xxx)
        $tableName = str_replace(['table_', '.sql'], '', $file);
        
        // Check if table exists
        try {
            $stmt = $this->pdo->prepare("SHOW TABLES LIKE :tableName");
            $stmt->execute(['tableName' => $tableName]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            $this->logError("Error checking if table exists: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Log message to file
     * 
     * @param string $message Message to log
     */
    private function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] $message" . PHP_EOL;
        file_put_contents($this->logFile, $logMessage, FILE_APPEND);
    }
    
    /**
     * Log error message to file
     * 
     * @param string $message Error message to log
     */
    private function logError($message) {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] ERROR: $message" . PHP_EOL;
        file_put_contents($this->logFile, $logMessage, FILE_APPEND);
    }
    
    /**
     * Get all error messages
     * 
     * @return array Array of error messages
     */
    public function getErrors() {
        return $this->errors;
    }
    
    /**
     * Check if installation has errors
     * 
     * @return bool True if errors exist, false otherwise
     */
    public function hasErrors() {
        return !empty($this->errors);
    }
}
