<?php
/**
 * Database Configuration for Shebamiles EMS
 */

// Database credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'shebamiles_ems_new');

// Create database connection
class Database {
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = DB_NAME;
    private $conn;
    private $error;

    // CONSTRUCTOR: Called when new Database() is instantiated
    // Automatically establishes database connection
    public function __construct() {
        $this->connect();
    }

    // CONNECT METHOD: Establish database connection using PDO
    // This is called automatically when Database class is instantiated
    private function connect() {
        // Initialize connection as null
        $this->conn = null;
        
        try {
            // BUILD DSN (Data Source Name) for PDO connection
            // Format: mysql:host=localhost;dbname=database_name;charset=utf8mb4
            // charset=utf8mb4 ensures proper Unicode character support
            $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->dbname . ';charset=utf8mb4';
            
            // SET PDO OPTIONS for security and consistency
            // ATTR_ERRMODE=EXCEPTION: Throw exceptions on errors (don't suppress)
            // ATTR_DEFAULT_FETCH_MODE=ASSOC: Return results as associative arrays
            // ATTR_EMULATE_PREPARES=false: Use native prepared statements (safer)
            $options = array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            );
            
            // CREATE new PDO connection with credentials and options
            // Throws PDOException if connection fails (caught below)
            $this->conn = new PDO($dsn, $this->user, $this->pass, $options);
            
        } catch(PDOException $e) {
            // CATCH database connection errors
            $this->error = $e->getMessage();
            
            // LOG ERROR in development environment only
            // In production, don't expose database errors to maintain security
            if (isset($_SERVER['SERVER_NAME']) && $_SERVER['SERVER_NAME'] === 'localhost') {
                error_log("Database Connection Error: " . $this->error);
            }
        }
        
        // RETURN the connection object (or null if failed)
        return $this->conn;
    }

    public function getConnection() {
        return $this->conn;
    }
}
?>
