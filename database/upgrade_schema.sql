-- ========================================
-- SHEBAMILES EMS - ENHANCED FEATURES UPGRADE
-- Run this after importing shebamiles_db.sql
-- ========================================

USE `shebamiles_ems_new`;

-- --------------------------------------------------------
-- Table: documents (Employee document management)
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `documents` (
  `document_id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `document_type` enum('id_card','certificate','contract','resume','other') NOT NULL,
  `document_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_size` int(11) DEFAULT NULL,
  `uploaded_by` int(11) NOT NULL,
  `upload_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`document_id`),
  KEY `employee_id` (`employee_id`),
  KEY `uploaded_by` (`uploaded_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: company_settings (System configuration)
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `company_settings` (
  `setting_id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_group` varchar(50) DEFAULT 'general',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`setting_id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: activity_log (Audit trail)
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `activity_log` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `entity_type` varchar(50) DEFAULT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_id`),
  KEY `user_id` (`user_id`),
  KEY `created_at` (`created_at`),
  KEY `entity_type` (`entity_type`, `entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: holidays (Company holidays)
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `holidays` (
  `holiday_id` int(11) NOT NULL AUTO_INCREMENT,
  `holiday_name` varchar(100) NOT NULL,
  `holiday_date` date NOT NULL,
  `is_recurring` tinyint(1) NOT NULL DEFAULT 0,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`holiday_id`),
  KEY `holiday_date` (`holiday_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: leave_balance (Employee leave entitlements)
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `leave_balance` (
  `balance_id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `leave_type` enum('sick','vacation','personal','emergency','other') NOT NULL,
  `year` int(4) NOT NULL,
  `total_days` int(11) NOT NULL DEFAULT 0,
  `used_days` int(11) NOT NULL DEFAULT 0,
  `remaining_days` int(11) GENERATED ALWAYS AS (`total_days` - `used_days`) STORED,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`balance_id`),
  UNIQUE KEY `employee_leave_year` (`employee_id`, `leave_type`, `year`),
  KEY `employee_id` (`employee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: salary_history (Track salary changes)
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `salary_history` (
  `history_id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `old_salary` decimal(10,2) NOT NULL,
  `new_salary` decimal(10,2) NOT NULL,
  `effective_date` date NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `changed_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`history_id`),
  KEY `employee_id` (`employee_id`),
  KEY `changed_by` (`changed_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: announcements (Company-wide announcements)
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `announcements` (
  `announcement_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `priority` enum('low','normal','high','urgent') NOT NULL DEFAULT 'normal',
  `target_audience` enum('all','admins','managers','employees','department') NOT NULL DEFAULT 'all',
  `department_id` int(11) DEFAULT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT 1,
  `publish_date` date DEFAULT NULL,
  `expire_date` date DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`announcement_id`),
  KEY `created_by` (`created_by`),
  KEY `department_id` (`department_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: timesheet (Detailed time tracking)
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `timesheet` (
  `timesheet_id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `project_name` varchar(200) DEFAULT NULL,
  `hours_worked` decimal(5,2) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`timesheet_id`),
  KEY `employee_id` (`employee_id`),
  KEY `approved_by` (`approved_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Update: notifications table (Enhanced structure)
-- --------------------------------------------------------

ALTER TABLE `notifications` 
  ADD COLUMN IF NOT EXISTS `link` varchar(255) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `read_at` timestamp NULL DEFAULT NULL,
  ADD INDEX IF NOT EXISTS `link` (`link`);

-- --------------------------------------------------------
-- Foreign key constraints
-- --------------------------------------------------------

ALTER TABLE `documents`
  ADD CONSTRAINT `documents_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `documents_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`user_id`) ON DELETE RESTRICT;

ALTER TABLE `activity_log`
  ADD CONSTRAINT `activity_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

ALTER TABLE `leave_balance`
  ADD CONSTRAINT `leave_balance_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE;

ALTER TABLE `salary_history`
  ADD CONSTRAINT `salary_history_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `salary_history_ibfk_2` FOREIGN KEY (`changed_by`) REFERENCES `users` (`user_id`) ON DELETE RESTRICT;

ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `announcements_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`) ON DELETE CASCADE;

ALTER TABLE `timesheet`
  ADD CONSTRAINT `timesheet_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `timesheet_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

-- --------------------------------------------------------
-- Insert default company settings
-- --------------------------------------------------------

INSERT INTO `company_settings` (`setting_key`, `setting_value`, `setting_group`) VALUES
('company_name', 'Shebamiles', 'general'),
('company_email', 'info@shebamiles.com', 'general'),
('company_phone', '+234-000-0000', 'general'),
('company_address', 'Lagos, Nigeria', 'general'),
('working_hours_start', '09:00', 'attendance'),
('working_hours_end', '17:00', 'attendance'),
('late_threshold_minutes', '15', 'attendance'),
('sick_leave_days', '10', 'leave'),
('vacation_leave_days', '20', 'leave'),
('personal_leave_days', '5', 'leave'),
('enable_email_notifications', '1', 'notifications'),
('theme', 'light', 'appearance'),
('date_format', 'Y-m-d', 'general'),
('currency_symbol', '₦', 'payroll')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

-- --------------------------------------------------------
-- Insert sample holidays for 2026
-- --------------------------------------------------------

INSERT INTO `holidays` (`holiday_name`, `holiday_date`, `is_recurring`, `description`) VALUES
('New Year Day', '2026-01-01', 1, 'New Year Celebration'),
('Good Friday', '2026-04-03', 0, 'Easter Holiday'),
('Easter Monday', '2026-04-06', 0, 'Easter Holiday'),
('Workers Day', '2026-05-01', 1, 'International Workers Day'),
('Democracy Day', '2026-06-12', 1, 'Democracy Day'),
('Eid al-Fitr', '2026-07-06', 0, 'Islamic Holiday'),
('Independence Day', '2026-10-01', 1, 'Nigeria Independence Day'),
('Christmas Day', '2026-12-25', 1, 'Christmas Celebration'),
('Boxing Day', '2026-12-26', 1, 'Day after Christmas')
ON DUPLICATE KEY UPDATE holiday_name = VALUES(holiday_name);

-- --------------------------------------------------------
-- Initialize leave balance for existing employees
-- --------------------------------------------------------

INSERT INTO `leave_balance` (`employee_id`, `leave_type`, `year`, `total_days`, `used_days`)
SELECT 
    e.employee_id,
    'vacation' as leave_type,
    2026 as year,
    20 as total_days,
    0 as used_days
FROM employees e
WHERE NOT EXISTS (
    SELECT 1 FROM leave_balance lb 
    WHERE lb.employee_id = e.employee_id 
    AND lb.leave_type = 'vacation' 
    AND lb.year = 2026
);

INSERT INTO `leave_balance` (`employee_id`, `leave_type`, `year`, `total_days`, `used_days`)
SELECT 
    e.employee_id,
    'sick' as leave_type,
    2026 as year,
    10 as total_days,
    0 as used_days
FROM employees e
WHERE NOT EXISTS (
    SELECT 1 FROM leave_balance lb 
    WHERE lb.employee_id = e.employee_id 
    AND lb.leave_type = 'sick' 
    AND lb.year = 2026
);

INSERT INTO `leave_balance` (`employee_id`, `leave_type`, `year`, `total_days`, `used_days`)
SELECT 
    e.employee_id,
    'personal' as leave_type,
    2026 as year,
    5 as total_days,
    0 as used_days
FROM employees e
WHERE NOT EXISTS (
    SELECT 1 FROM leave_balance lb 
    WHERE lb.employee_id = e.employee_id 
    AND lb.leave_type = 'personal' 
    AND lb.year = 2026
);

COMMIT;

-- ========================================
-- UPGRADE COMPLETE!
-- ========================================
-- New features enabled:
-- ✓ Document Management
-- ✓ Activity Logging
-- ✓ Holiday Management
-- ✓ Leave Balance Tracking
-- ✓ Salary History
-- ✓ Company Settings
-- ✓ Announcements
-- ✓ Timesheet Tracking
-- ========================================
