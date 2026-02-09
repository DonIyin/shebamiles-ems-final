# Shebamiles EMS - API Reference & Functions Documentation

## Overview

This document provides a comprehensive reference for all PHP functions, classes, and their usage patterns in the Shebamiles EMS system.

## Core Classes

### Database Class

**File**: `includes/config.php`

**Purpose**: Manages database connection using PDO

```php
// Constructor
$db = new Database();

// Get connection object
$conn = $db->getConnection();
// Returns: PDO connection object or null on failure
```

**Internal Implementation**:
```php
class Database {
    // Private static instance (Singleton pattern)
    private $host = 'localhost';
    private $user = 'root';
    private $pass = '';
    private $dbname = 'shebamiles_ems_new';
    private $conn;
    
    // Connection setup with error handling
    private function connect() {
        // Uses PDO with utf8mb4 charset
        // Sets error mode to exceptions
        // Disables prepared statement emulation
    }
}
```

**Usage Pattern**:
```php
// Typical data access
$db = new Database();
$conn = $db->getConnection();

if ($conn === null) {
    die('Database connection failed');
}

$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();
```

**Error Handling**:
- Throws `PDOException` on connection failures
- Logs errors (in localhost environment)
- Returns null connection on database unavailability

---

## Authentication Functions

**File**: `includes/auth.php`

### isLoggedIn()

```php
boolean isLoggedIn()
```

**Purpose**: Check if user is currently authenticated

**Returns**: 
- `true` if user has valid session
- `false` if session is not set or expired

**Usage**:
```php
if (isLoggedIn()) {
    // User is logged in
} else {
    // Redirect to login
    header('Location: login.php');
}
```

**Implementation Details**:
- Checks if `$_SESSION['user_id']` is set
- Checks if `$_SESSION['username']` is set
- Both must exist for valid session

---

### hasRole($role)

```php
boolean hasRole(string $role)
```

**Purpose**: Verify if current user has specific role

**Parameters**:
- `$role` (string): Role to check - 'admin', 'manager', or 'employee'

**Returns**: 
- `true` if user's role matches
- `false` otherwise

**Usage**:
```php
if (hasRole('admin')) {
    // Show admin-only features
}

if (hasRole('employee')) {
    // Show employee features
}
```

**Example**:
```php
// Check multiple roles
if (hasRole('admin') || hasRole('manager')) {
    // Show approval features
}
```

---

### requireLogin()

```php
void requireLogin()
```

**Purpose**: Middleware function - terminates if user not logged in

**Returns**: void (exits script with redirect if not logged in)

**Usage**:
```php
<?php
require_once 'includes/auth.php';
requireLogin();  // Must be first!

// If we reach here, user is guaranteed to be logged in
?>
```

**Behind the Scenes**:
```
Check isLoggedIn()
    ↓
If false:
    - Redirect to login.php
    - Exit script
If true:
    - Continue execution
```

---

### requireAdmin()

```php
void requireAdmin()
```

**Purpose**: Middleware function - terminates if user is not admin

**Returns**: void (redirects with exit if insufficient permissions)

**Usage**:
```php
<?php
require_once 'includes/auth.php';
requireAdmin();  // Call at top of admin-only pages

// Only admin users reach this point
?>
```

**Flow**:
```
Call requireLogin() first
    ↓
Check hasRole('admin')
    ↓
If false:
    - Redirect to dashboard.php
    - Exit script
If true:
    - Continue
```

---

### login($username, $password)

```php
boolean login(string $username, string $password)
```

**Purpose**: Authenticate user and create session

**Parameters**:
- `$username` (string): Username to authenticate
- `$password` (string): Plain-text password (will be verified against hash)

**Returns**:
- `true` on successful authentication
- `false` on failed credentials or database error

**Usage**:
```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    if (login($username, $password)) {
        // Session created, redirect
        header('Location: dashboard.php');
    } else {
        // Show error message
        $error = 'Invalid credentials';
    }
}
```

**Complex Logic Explanation**:

```php
function login($username, $password) {
    // 1. Connect to database
    $db = new Database();
    $conn = $db->getConnection();
    if ($conn === null) return false;  // DB unavailable
    
    try {
        // 2. Query with JOIN to get employee info
        // Uses LEFT JOIN employees because not all users may have employee records
        $stmt = $conn->prepare(
            "SELECT u.*, e.employee_id, e.first_name, e.last_name 
             FROM users u 
             LEFT JOIN employees e ON u.user_id = e.user_id 
             WHERE u.username = ? AND u.status = 'active'"
        );
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        // 3. Verify password using bcrypt comparison
        // password_verify() is constant-time to prevent timing attacks
        if ($user && password_verify($password, $user['password'])) {
            // 4. Set multiple session variables for later access
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['employee_id'] = $user['employee_id'];
            $_SESSION['full_name'] = $user['first_name'] . ' ' . $user['last_name'];
            
            // 5. Update last_login timestamp for audit
            $updateStmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
            $updateStmt->execute([$user['user_id']]);
            
            return true;  // Success
        }
        
        return false;  // Credentials don't match
    } catch(PDOException $e) {
        return false;  // Database error
    }
}
```

**Security Features**:
- Only inactive users are excluded (`status = 'active'`)
- Password verification uses `password_verify()` (constant-time comparison)
- Database exceptions don't expose details
- Last login logged for audit

---

### logout()

```php
void logout()
```

**Purpose**: Destroy session and clear authentication

**Returns**: void (redirects to login with exit)

**Usage**:
```php
// User clicks logout
logout();  // Function terminates execution
```

**What It Does**:
```php
function logout() {
    session_unset();      // Remove all $_SESSION variables
    session_destroy();    // Destroy session file on server
    header('Location: ../login.php');
    exit();               // Terminate script
}
```

---

### getCurrentUser()

```php
array|null getCurrentUser()
```

**Purpose**: Get current user's information from session

**Returns**: 
- Array with keys: user_id, username, email, role, employee_id, full_name
- null if not logged in

**Usage**:
```php
$user = getCurrentUser();

if ($user) {
    echo "Welcome, " . $user['full_name'];
    echo "Role: " . $user['role'];
    
    if ($user['employee_id']) {
        // User has an employee record
    }
}
```

**Return Value Structure**:
```php
[
    'user_id' => 1,
    'username' => 'john_doe',
    'email' => 'john@example.com',
    'role' => 'manager',
    'employee_id' => 5,
    'full_name' => 'John Doe'
]
```

---

### sanitize($input)

```php
string sanitize(string $input)
```

**Purpose**: Clean user input to prevent XSS attacks

**Parameters**:
- `$input` (string): Raw user input to sanitize

**Returns**: Sanitized string safe for display

**Usage**:
```php
// User input from form
$username = sanitize($_POST['username']);
$email = sanitize($_POST['email']);

if (empty($username) || empty($email)) {
    $error = 'Please fill all fields';
}
```

**What It Does**:
```php
function sanitize($input) {
    // Strip all tags and special characters
    // Equivalent to: trim() + strip_tags()
    return trim(strip_tags($input));
}
```

**Important Note**: 
- Sanitize removes HTML/tags but doesn't prevent SQL injection
- Use prepared statements (with ?) for SQL safety
- Don't rely on sanitize() alone for security

---

## Helper Functions

**File**: `includes/helpers.php`

### getSetting($key, $default = null)

```php
string|null getSetting(string $key, string $default = null)
```

**Purpose**: Retrieve company settings with automatic caching

**Parameters**:
- `$key` (string): Setting name (e.g., 'company_name')
- `$default` (string): Value if setting not found

**Returns**: Setting value or default

**Usage**:
```php
$companyName = getSetting('company_name', 'Shebamiles');
$timezone = getSetting('timezone', 'UTC');
$workingDays = getSetting('working_days', '5');
```

**Advanced Implementation** (Caching):

```php
function getSetting($key, $default = null) {
    // Static variable caches settings in memory
    // Only queries database once per page load
    static $settings = null;
    
    if ($settings === null) {
        // First call: load all settings from database
        $db = new Database();
        $conn = $db->getConnection();
        
        if ($conn === null) {
            return $default;  // DB error, return default
        }
        
        try {
            // Load entire settings table once
            // FETCH_KEY_PAIR returns array with key=>value format
            $stmt = $conn->query("SELECT setting_key, setting_value FROM company_settings");
            $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch(PDOException $e) {
            return $default;  // Query failed
        }
    }
    
    // ternary operator: key exists? return it : return default
    return isset($settings[$key]) ? $settings[$key] : $default;
}
```

**Performance Note**:
- First call loads all settings (one query)
- Subsequent calls use cached data (no database hits)
- Improves performance on pages with multiple getSetting() calls

---

### updateSetting($key, $value, $userId = null)

```php
boolean updateSetting(string $key, string $value, int $userId = null)
```

**Purpose**: Create or update a company setting

**Parameters**:
- `$key` (string): Setting name
- `$value` (string): New value
- `$userId` (int): User making the change (for audit)

**Returns**: true if successful, false otherwise

**Usage**:
```php
// Update setting from settings page
if (updateSetting('company_name', 'New Name', $currentUserId)) {
    $success = 'Setting updated';
} else {
    $error = 'Failed to update setting';
}
```

**Database Logic** (INSERT...ON DUPLICATE KEY UPDATE):

```php
function updateSetting($key, $value, $userId = null) {
    $db = new Database();
    $conn = $db->getConnection();
    
    if ($conn === null) return false;
    
    try {
        // MySQL upsert pattern:
        // If key exists: UPDATE value
        // If key doesn't exist: INSERT new row
        // Requires unique constraint on setting_key
        
        $stmt = $conn->prepare(
            "INSERT INTO company_settings (setting_key, setting_value, updated_by) 
             VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE 
                setting_value = ?, 
                updated_by = ?"
        );
        
        // Execute with: insert_key, insert_value, insert_user, update_value, update_user
        $stmt->execute([$key, $value, $userId, $value, $userId]);
        return true;
    } catch(PDOException $e) {
        return false;
    }
}
```

---

### logActivity($userId, $action, $entityType, $entityId, $description)

```php
boolean logActivity(
    int $userId, 
    string $action, 
    string $entityType = null, 
    int $entityId = null, 
    string $description = null
)
```

**Purpose**: Create audit trail of system actions

**Parameters**:
- `$userId` (int): User performing action
- `$action` (string): Action type (create, update, delete, view)
- `$entityType` (string): What was affected (employee, attendance, payroll)
- `$entityId` (int): ID of affected record
- `$description` (string): Additional details

**Returns**: true if logged, false if error

**Usage Examples**:
```php
// Employee created
logActivity($userId, 'create', 'employee', $newEmployeeId, 'Created new employee');

// Attendance marked
logActivity($userId, 'update', 'attendance', $attendanceId, 'Marked present');

// Leave request approved
logActivity($userId, 'update', 'leave_request', $leaveId, 'Approved by manager');

// Salary increased
logActivity($userId, 'update', 'employee', $empId, 'Salary increased to $5000');
```

**What It Records**:
```php
function logActivity($userId, $action, $entityType = null, $entityId = null, $description = null) {
    $db = new Database();
    $conn = $db->getConnection();
    
    if ($conn === null) return false;
    
    try {
        // Capture request context
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;      // User's IP
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;  // Browser info
        
        $stmt = $conn->prepare(
            "INSERT INTO activity_log 
            (user_id, action, entity_type, entity_id, description, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        
        $stmt->execute([$userId, $action, $entityType, $entityId, $description, $ipAddress, $userAgent]);
        return true;
    } catch(PDOException $e) {
        return false;
    }
}
```

**Audit Trail Benefits**:
- Track who did what and when
- Compliance with regulations
- Security investigation
- Change history

---

### getRecentActivity($limit = 50, $userId = null)

```php
array getRecentActivity(int $limit = 50, int $userId = null)
```

**Purpose**: Retrieve recent activity logs with user information

**Parameters**:
- `$limit` (int): Maximum records to return (default 50)
- `$userId` (int): Filter by specific user (optional)

**Returns**: Array of activity records with user details

**Usage**:
```php
// All recent activity
$allActivity = getRecentActivity(20);

// Specific user's activity
$userActivity = getRecentActivity(100, $userId);
```

**Returned Structure**:
```php
[
    [
        'log_id' => 1,
        'user_id' => 3,
        'username' => 'john_doe',
        'role' => 'admin',
        'action' => 'create',
        'entity_type' => 'employee',
        'entity_id' => 42,
        'description' => 'Created new employee',
        'ip_address' => '192.168.1.100',
        'user_agent' => 'Mozilla/5.0...',
        'created_at' => '2026-02-09 10:30:00'
    ],
    // ... more records
]
```

---

### isHoliday($date = null)

```php
boolean isHoliday(string $date = null)
```

**Purpose**: Check if specific date is a company holiday

**Parameters**:
- `$date` (string): Date to check, format 'YYYY-MM-DD' (default: today)

**Returns**: true if holiday, false otherwise

**Usage**:
```php
// Check if today is holiday
if (isHoliday()) {
    echo "Office is closed today";
}

// Check specific date
if (isHoliday('2026-12-25')) {
    echo "Christmas is a holiday";
}
```

**Implementation**:
```php
function isHoliday($date = null) {
    $date = $date ?? date('Y-m-d');  // Use today if not provided
    
    $db = new Database();
    $conn = $db->getConnection();
    
    if ($conn === null) return false;
    
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM holidays WHERE holiday_date = ?");
        $stmt->execute([$date]);
        return $stmt->fetchColumn() > 0;  // Returns true if count > 0
    } catch(PDOException $e) {
        return false;
    }
}
```

---

### hasPermission($permission)

```php
boolean hasPermission(string $permission)
```

**Purpose**: Check if current user has specific permission

**Parameters**:
- `$permission` (string): Permission to check

**Returns**: true if user has permission, false otherwise

**Common Permissions**:
- `view_analytics` - Access dashboard and reports
- `manage_attendance` - Mark attendance
- `approve_leaves` - Approve leave requests
- `manage_payroll` - Process payroll
- `manage_users` - Create/edit users
- `manage_settings` - Change system settings

**Usage**:
```php
if (hasPermission('manage_payroll')) {
    // Show payroll menu
    echo '<a href="payroll.php">Payroll</a>';
}

if (hasPermission('approve_leaves')) {
    // Show leave approval section
}
```

**Permission Mapping** (in function):
```php
function hasPermission($permission) {
    $user = getCurrentUser();
    
    if (!$user) return false;
    
    // Define permission matrix by role
    $permissions = [
        'admin' => [
            'view_analytics',
            'manage_employees',
            'manage_attendance',
            'approve_leaves',
            'manage_payroll',
            'manage_users',
            'manage_settings'
        ],
        'manager' => [
            'view_analytics',
            'manage_employees',
            'manage_attendance',
            'approve_leaves'
        ],
        'employee' => [
            'view_profile',
            'request_leave',
            'view_payroll'
        ]
    ];
    
    return in_array($permission, $permissions[$user['role']] ?? []);
}
```

---

## Page Load Patterns

### Typical Admin Page Structure

```php
<?php
// 1. Include required files
require_once 'includes/auth.php';
require_once 'includes/helpers.php';

// 2. Check permissions early (acts as middleware)
requireAdmin();

// Or for specific permission:
if (!hasPermission('manage_employees')) {
    header('Location: dashboard.php');
    exit();
}

// 3. Database connection
$db = new Database();
$conn = $db->getConnection();

// 4. Handle POST requests (form submissions)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $name = sanitize($_POST['name'] ?? '');
    
    // Validate
    if (empty($name)) {
        $error = 'Name is required';
    } else {
        try {
            // Execute query
            $stmt = $conn->prepare("INSERT INTO ...");
            $stmt->execute([...]);
            
            // Log action
            logActivity($user['user_id'], 'create', 'entity_type', $lastId);
            
            // Redirect on success
            header('Location: ...');
            exit();
        } catch(PDOException $e) {
            $error = 'Database error';
        }
    }
}

// 5. Fetch data for display
$stmt = $conn->query("SELECT ...");
$data = $stmt->fetchAll();

// 6. HTML output
?>
<!DOCTYPE html>
...
```

---

## Common Workflow Examples

### Creating a New Record

```php
// 1. Get current user
$user = getCurrentUser();

// 2. Validate input
$name = sanitize($_POST['name'] ?? '');
if (empty($name)) {
    $error = 'Name required';
} else {
    // 3. Prepare database connection
    $db = new Database();
    $conn = $db->getConnection();
    
    try {
        // 4. Insert record
        $stmt = $conn->prepare("INSERT INTO employees (first_name) VALUES (?)");
        $stmt->execute([$name]);
        
        // 5. Get new ID
        $newId = $conn->lastInsertId();
        
        // 6. Log activity
        logActivity($user['user_id'], 'create', 'employee', $newId, "Created $name");
        
        // 7. Send notification (if function exists)
        // createNotification($user['role'], 'New employee added', '...');
        
        $success = 'Employee created';
    } catch(PDOException $e) {
        $error = 'Error creating employee';
    }
}
```

### Approving a Leave Request

```php
$user = getCurrentUser();

if (hasPermission('approve_leaves')) {
    $leaveId = (int)$_POST['leave_id'];
    $approved = $_POST['approved'] === '1';
    $notes = sanitize($_POST['notes'] ?? '');
    
    $db = new Database();
    $conn = $db->getConnection();
    
    try {
        // Update leave status
        $status = $approved ? 'approved' : 'rejected';
        $stmt = $conn->prepare(
            "UPDATE leave_requests 
             SET status = ?, approved_by = ?, approval_notes = ? 
             WHERE leave_id = ?"
        );
        $stmt->execute([$status, $user['user_id'], $notes, $leaveId]);
        
        // If approved, mark attendance as 'leave'
        if ($approved) {
            // Get leave dates
            $stmt = $conn->prepare("SELECT start_date, end_date FROM leave_requests WHERE leave_id = ?");
            $stmt->execute([$leaveId]);
            $leave = $stmt->fetch();
            
            // Mark all dates in range
            // ... loop and insert attendance records
        }
        
        // Log the action
        logActivity($user['user_id'], 'update', 'leave_request', $leaveId, 
                   "Marked as $status");
        
        $success = 'Leave request ' . $status;
    } catch(PDOException $e) {
        $error = 'Error processing request';
    }
}
```

---

## Error Handling Best Practices

### Database Errors
```php
try {
    $stmt = $conn->prepare("SELECT ...");
    $stmt->execute($params);
} catch(PDOException $e) {
    // Log full error detail for debugging
    error_log("Database error: " . $e->getMessage());
    
    // Show user-friendly message
    $error = 'An unexpected error occurred. Please try again later.';
}
```

### Authentication Errors
```php
if (login($username, $password)) {
    // Success
} else {
    // Don't reveal whether username exists for security
    $error = 'Invalid username or password';
}
```

### Validation Errors
```php
$errors = [];

if (empty($name)) {
    $errors[] = 'Name is required';
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Valid email is required';
}

if (!empty($errors)) {
    $error = implode('<br>', $errors);
}
```

---

**Last Updated**: February 2026  
**API Version**: 1.0
