<?php
/**
 * Authentication Functions for Shebamiles EMS
 */

session_start();
require_once 'config.php';

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

// Check user role
function hasRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

// Redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

// Redirect if not admin
function requireAdmin() {
    requireLogin();
    if (!hasRole('admin')) {
        header('Location: dashboard.php');
        exit();
    }
}

// Login function
function login($username, $password) {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Check if database connection is available
    if ($conn === null) {
        return false;
    }
    
    try {
        $stmt = $conn->prepare("SELECT u.*, e.employee_id, e.first_name, e.last_name 
                                FROM users u 
                                LEFT JOIN employees e ON u.user_id = e.user_id 
                                WHERE u.username = ? AND u.status = 'active'");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['employee_id'] = $user['employee_id'];
            $_SESSION['full_name'] = $user['first_name'] . ' ' . $user['last_name'];
            
            // Update last login
            $updateStmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
            $updateStmt->execute([$user['user_id']]);
            
            return true;
        }
        
        return false;
    } catch(PDOException $e) {
        return false;
    }
}

// Logout function
function logout() {
    session_unset();
    session_destroy();
    header('Location: ../login.php');
    exit();
}

// Get current user info
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'user_id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'email' => $_SESSION['email'],
        'role' => $_SESSION['role'],
        'employee_id' => $_SESSION['employee_id'] ?? null,
        'full_name' => $_SESSION['full_name'] ?? 'User'
    ];
}

// Sanitize input
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Generate CSRF token
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF token
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * ROLE-BASED ACCESS CONTROL (RBAC) SYSTEM
 * Defines permissions for Admin and Employee roles
 */

// Get all permissions for a role
function getPermissions($role = null) {
    if ($role === null && isLoggedIn()) {
        $role = $_SESSION['role'] ?? 'employee';
    }
    
    $permissions = [
        'admin' => [
            // Dashboard & Core
            'view_dashboard' => true,
            'view_analytics' => true,
            
            // Employee Management
            'view_employees' => true,
            'create_employee' => true,
            'edit_employee' => true,
            'delete_employee' => true,
            'view_employee_details' => true,
            
            // Departments
            'view_departments' => true,
            'create_department' => true,
            'edit_department' => true,
            'delete_department' => true,
            
            // Attendance
            'view_attendance' => true,
            'manage_attendance' => true,
            'view_all_attendance' => true,
            
            // Leave Management
            'view_leaves' => true,
            'approve_leave' => true,
            'reject_leave' => true,
            'view_all_leaves' => true,
            
            // Payroll
            'view_payroll' => true,
            'create_payroll' => true,
            'edit_payroll' => true,
            'delete_payroll' => true,
            
            // Performance
            'view_performance' => true,
            'create_performance' => true,
            'edit_performance' => true,
            
            // User Management
            'view_users' => true,
            'create_user' => true,
            'edit_user' => true,
            'delete_user' => true,
            'manage_roles' => true,
            
            // System Administration
            'view_settings' => true,
            'edit_settings' => true,
            'view_activity_log' => true,
            'view_audit_trail' => true,
            
            // Documents
            'view_documents' => true,
            'upload_document' => true,
            'delete_document' => true,
            'view_all_documents' => true,
            'delete_others_documents' => true,
            
            // Announcements
            'view_announcements' => true,
            'create_announcement' => true,
            'edit_announcement' => true,
            'delete_announcement' => true,
            
            // Holiday Calendar
            'view_holidays' => true,
            'manage_holidays' => true,
            
            // Notifications
            'view_notifications' => true,
            'manage_notifications' => true,
        ],
        
        'employee' => [
            // Dashboard & Core
            'view_dashboard' => true,
            'view_analytics' => false,
            
            // Employee Management
            'view_employees' => true,  // Limited: only basic info
            'create_employee' => false,
            'edit_employee' => false,
            'delete_employee' => false,
            'view_employee_details' => false,  // Can't view detailed profiles
            
            // Departments
            'view_departments' => true,  // Limited: view only
            'create_department' => false,
            'edit_department' => false,
            'delete_department' => false,
            
            // Attendance
            'view_attendance' => true,  // Own attendance only
            'manage_attendance' => false,
            'view_all_attendance' => false,
            
            // Leave Management
            'view_leaves' => true,  // Own leaves only
            'approve_leave' => false,
            'reject_leave' => false,
            'view_all_leaves' => false,
            
            // Payroll
            'view_payroll' => false,
            'create_payroll' => false,
            'edit_payroll' => false,
            'delete_payroll' => false,
            
            // Performance
            'view_performance' => false,
            'create_performance' => false,
            'edit_performance' => false,
            
            // User Management
            'view_users' => false,
            'create_user' => false,
            'edit_user' => false,
            'delete_user' => false,
            'manage_roles' => false,
            
            // System Administration
            'view_settings' => false,
            'edit_settings' => false,
            'view_activity_log' => false,
            'view_audit_trail' => false,
            
            // Documents
            'view_documents' => true,
            'upload_document' => true,
            'delete_document' => false,  // Can't delete
            'view_all_documents' => false,  // Own documents only
            'delete_others_documents' => false,
            
            // Announcements
            'view_announcements' => true,  // View only
            'create_announcement' => false,
            'edit_announcement' => false,
            'delete_announcement' => false,
            
            // Holiday Calendar
            'view_holidays' => true,  // View only
            'manage_holidays' => false,
            
            // Notifications
            'view_notifications' => true,
            'manage_notifications' => false,
        ]
    ];
    
    return $permissions[$role] ?? $permissions['employee'];
}

// Check if user has a specific permission
function hasPermission($permission) {
    if (!isLoggedIn()) {
        return false;
    }
    
    $role = $_SESSION['role'] ?? 'employee';
    $permissions = getPermissions($role);
    
    return $permissions[$permission] ?? false;
}

// Require specific permission
function requirePermission($permission) {
    if (!hasPermission($permission)) {
        header('HTTP/1.0 403 Forbidden');
        echo '<!DOCTYPE html>
        <html>
        <head>
            <title>Access Denied</title>
            <style>
                body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
                .deny-box { background: #f8d7da; color: #721c24; padding: 20px; border-radius: 8px; max-width: 500px; margin: 0 auto; }
                h1 { color: #721c24; }
                a { color: #FF6B35; text-decoration: none; margin-top: 20px; display: inline-block; }
            </style>
        </head>
        <body>
            <div class="deny-box">
                <h1>🚫 Access Denied</h1>
                <p>You do not have permission to access this resource.</p>
                <a href="dashboard.php">→ Back to Dashboard</a>
            </div>
        </body>
        </html>';
        exit();
    }
}

// Get role display name
function getRoleDisplayName($role = null) {
    if ($role === null && isLoggedIn()) {
        $role = $_SESSION['role'] ?? 'employee';
    }
    
    return match(strtolower($role)) {
        'admin' => 'Administrator',
        'employee' => 'Employee',
        default => 'User'
    };
}

// List all available roles
function getAvailableRoles() {
    return [
        'admin' => 'Administrator (Full Access)',
        'employee' => 'Employee (Limited Access)'
    ];
}

?>

