<?php
/**
 * Fix Login - Update Admin Password
 * Run this script once to fix the login issue
 */

require_once 'includes/config.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Check if connection was successful
    if ($conn === null) {
        throw new Exception("Database connection failed. Please check if the database exists.");
    }
    
    // Generate correct password hash for 'admin123'
    $password = 'admin123';
    $hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Update the admin user password
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
    $stmt->execute([$hash]);
    
    if ($stmt->rowCount() > 0) {
        echo "✓ Success! Admin password has been updated.\n";
        echo "You can now login with:\n";
        echo "Username: admin\n";
        echo "Password: admin123\n";
    } else {
        echo "⚠ No admin user found. Please import the database first.\n";
        echo "Import the file: database/shebamiles_db.sql\n";
    }
    
} catch(PDOException $e) {
    echo "✗ Database Error: " . $e->getMessage() . "\n\n";
    if (strpos($e->getMessage(), 'Unknown database') !== false) {
        echo "The database 'shebamiles_ems_new' does not exist.\n\n";
        echo "SOLUTION:\n";
        echo "1. Start XAMPP and make sure MySQL is running\n";
        echo "2. Open phpMyAdmin (http://localhost/phpmyadmin)\n";
        echo "3. Click 'Import' tab\n";
        echo "4. Choose file: database/shebamiles_db.sql\n";
        echo "5. Click 'Go' to import\n";
        echo "6. Then run this script again\n";
    }
} catch(Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
?>
