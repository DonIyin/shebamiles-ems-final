<?php
/**
 * System Settings Helper Functions
 * Manages company settings and configuration
 */

// Get a specific setting value
function getSetting($key, $default = null) {
    static $settings = null;
    
    if ($settings === null) {
        $db = new Database();
        $conn = $db->getConnection();
        
        if ($conn === null) {
            return $default;
        }
        
        try {
            $stmt = $conn->query("SELECT setting_key, setting_value FROM company_settings");
            $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch(PDOException $e) {
            return $default;
        }
    }
    
    return isset($settings[$key]) ? $settings[$key] : $default;
}

// Update a setting value
function updateSetting($key, $value, $userId = null) {
    $db = new Database();
    $conn = $db->getConnection();
    
    if ($conn === null) {
        return false;
    }
    
    try {
        $stmt = $conn->prepare("INSERT INTO company_settings (setting_key, setting_value, updated_by) 
                               VALUES (?, ?, ?)
                               ON DUPLICATE KEY UPDATE setting_value = ?, updated_by = ?");
        $stmt->execute([$key, $value, $userId, $value, $userId]);
        return true;
    } catch(PDOException $e) {
        return false;
    }
}

// Get all settings by group
function getSettingsByGroup($group = 'general') {
    $db = new Database();
    $conn = $db->getConnection();
    
    if ($conn === null) {
        return [];
    }
    
    try {
        $stmt = $conn->prepare("SELECT setting_key, setting_value 
                               FROM company_settings 
                               WHERE setting_group = ?");
        $stmt->execute([$group]);
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    } catch(PDOException $e) {
        return [];
    }
}

// Log activity
function logActivity($userId, $action, $entityType = null, $entityId = null, $description = null) {
    $db = new Database();
    $conn = $db->getConnection();
    
    if ($conn === null) {
        return false;
    }
    
    try {
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        
        $stmt = $conn->prepare("INSERT INTO activity_log 
                               (user_id, action, entity_type, entity_id, description, ip_address, user_agent) 
                               VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $action, $entityType, $entityId, $description, $ipAddress, $userAgent]);
        return true;
    } catch(PDOException $e) {
        return false;
    }
}

// Get recent activity logs
function getRecentActivity($limit = 50, $userId = null) {
    $db = new Database();
    $conn = $db->getConnection();
    
    if ($conn === null) {
        return [];
    }
    
    try {
        $query = "SELECT al.*, u.username, u.role 
                  FROM activity_log al
                  JOIN users u ON al.user_id = u.user_id";
        
        if ($userId) {
            $query .= " WHERE al.user_id = ?";
        }
        
        $query .= " ORDER BY al.created_at DESC LIMIT ?";
        
        $stmt = $conn->prepare($query);
        if ($userId) {
            $stmt->execute([$userId, $limit]);
        } else {
            $stmt->execute([$limit]);
        }
        
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        return [];
    }
}

// Check if today is a holiday
function isHoliday($date = null) {
    $date = $date ?? date('Y-m-d');
    
    $db = new Database();
    $conn = $db->getConnection();
    
    if ($conn === null) {
        return false;
    }
    
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM holidays WHERE holiday_date = ?");
        $stmt->execute([$date]);
        return $stmt->fetchColumn() > 0;
    } catch(PDOException $e) {
        return false;
    }
}

// Get upcoming holidays
function getUpcomingHolidays($limit = 5) {
    $db = new Database();
    $conn = $db->getConnection();
    
    if ($conn === null) {
        return [];
    }
    
    try {
        $stmt = $conn->prepare("SELECT * FROM holidays 
                               WHERE holiday_date >= CURDATE() 
                               ORDER BY holiday_date ASC 
                               LIMIT ?");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        return [];
    }
}

// Get employee leave balance
function getLeaveBalance($employeeId, $leaveType = null, $year = null) {
    $year = $year ?? date('Y');
    
    $db = new Database();
    $conn = $db->getConnection();
    
    if ($conn === null) {
        return [];
    }
    
    try {
        if ($leaveType) {
            $stmt = $conn->prepare("SELECT * FROM leave_balance 
                                   WHERE employee_id = ? AND leave_type = ? AND year = ?");
            $stmt->execute([$employeeId, $leaveType, $year]);
            return $stmt->fetch();
        } else {
            $stmt = $conn->prepare("SELECT * FROM leave_balance 
                                   WHERE employee_id = ? AND year = ?");
            $stmt->execute([$employeeId, $year]);
            return $stmt->fetchAll();
        }
    } catch(PDOException $e) {
        return $leaveType ? null : [];
    }
}

// Update leave balance
function updateLeaveBalance($employeeId, $leaveType, $daysUsed, $year = null) {
    $year = $year ?? date('Y');
    
    $db = new Database();
    $conn = $db->getConnection();
    
    if ($conn === null) {
        return false;
    }
    
    try {
        $stmt = $conn->prepare("UPDATE leave_balance 
                               SET used_days = used_days + ? 
                               WHERE employee_id = ? AND leave_type = ? AND year = ?");
        $stmt->execute([$daysUsed, $employeeId, $leaveType, $year]);
        return true;
    } catch(PDOException $e) {
        return false;
    }
}

// Create notification
function createNotification($userId, $title, $message, $type = 'info', $link = null) {
    $db = new Database();
    $conn = $db->getConnection();
    
    if ($conn === null) {
        return false;
    }
    
    try {
        $stmt = $conn->prepare("INSERT INTO notifications 
                               (user_id, title, message, type, link) 
                               VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $title, $message, $type, $link]);
        return true;
    } catch(PDOException $e) {
        return false;
    }
}

// Get unread notification count
function getUnreadNotificationCount($userId) {
    $db = new Database();
    $conn = $db->getConnection();
    
    if ($conn === null) {
        return 0;
    }
    
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM notifications 
                               WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    } catch(PDOException $e) {
        return 0;
    }
}

// Get user notifications
function getUserNotifications($userId, $limit = 10, $unreadOnly = false) {
    $db = new Database();
    $conn = $db->getConnection();
    
    if ($conn === null) {
        return [];
    }
    
    try {
        $query = "SELECT * FROM notifications WHERE user_id = ?";
        if ($unreadOnly) {
            $query .= " AND is_read = 0";
        }
        $query .= " ORDER BY created_at DESC LIMIT ?";
        
        $stmt = $conn->prepare($query);
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        return [];
    }
}

// Mark notification as read
function markNotificationRead($notificationId) {
    $db = new Database();
    $conn = $db->getConnection();
    
    if ($conn === null) {
        return false;
    }
    
    try {
        $stmt = $conn->prepare("UPDATE notifications 
                               SET is_read = 1, read_at = NOW() 
                               WHERE notification_id = ?");
        $stmt->execute([$notificationId]);
        return true;
    } catch(PDOException $e) {
        return false;
    }
}

// Format currency
function formatCurrency($amount) {
    $symbol = getSetting('currency_symbol', '₦');
    return $symbol . number_format($amount, 2);
}

// Get company info
function getCompanyInfo() {
    return [
        'name' => getSetting('company_name', 'Shebamiles'),
        'email' => getSetting('company_email', 'info@shebamiles.com'),
        'phone' => getSetting('company_phone', '+234-000-0000'),
        'address' => getSetting('company_address', 'Lagos, Nigeria')
    ];
}
?>
