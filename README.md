# Shebamiles Employee Management System

A modern, fully-functional Employee Management System built with PHP, MySQL, HTML, CSS, and JavaScript. Features a beautiful orange and white theme with a professional UI/UX design.

## Features

### Core Functionality
- **User Authentication**: Secure login system with role-based access (Admin, Manager, Employee)
- **Employee Management**: Complete CRUD operations for employee records
- **Department Management**: Create and manage company departments
- **Attendance Tracking**: Mark and monitor employee attendance with status tracking
- **Leave Management**: Submit, approve, and track leave requests
- **Payroll Management**: Create payroll records and track payments
- **Performance Reviews**: Record and review employee performance
- **User Management**: Admin-only user creation and password resets
- **Dashboard**: Beautiful analytics dashboard with key metrics
- **Role-Based Access Control**: Different permissions for different user types

### Technical Features
- Modern, responsive UI with orange/white Shebamiles branding
- Clean, professional design with smooth animations
- Secure password hashing
- Prepared statements for SQL injection prevention
- Session management
- CSRF protection ready
- Modular code structure

## Installation Instructions

### Prerequisites
- **XAMPP** (or WAMP/LAMP) installed on your computer
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web browser (Chrome, Firefox, Safari, Edge)

### Step-by-Step Installation

#### 1. Extract the Files
- Download the `shebamiles-ems.zip` file
- Extract it to your XAMPP `htdocs` folder
  - Windows: `C:\xampp\htdocs\shebamiles-ems`
  - Mac: `/Applications/XAMPP/htdocs/shebamiles-ems`
  - Linux: `/opt/lampp/htdocs/shebamiles-ems`

#### 2. Start XAMPP
- Open XAMPP Control Panel
- Start **Apache** server
- Start **MySQL** server

#### 3. Create the Database
- Open your web browser
- Go to: `http://localhost/phpmyadmin`
- Click on "Import" tab
- Click "Choose File" and select `database/shebamiles_db.sql` from the extracted folder
- Click "Go" at the bottom of the page
- Wait for the import to complete

**Alternative Method (Manual):**
- In phpMyAdmin, click "New" to create a database
- Name it `shebamiles_ems_new`
- Click on the database name
- Go to "SQL" tab
- Copy and paste the contents of `database/shebamiles_db.sql`
- Click "Go"

#### 4. Configure Database Connection (if needed)
The default settings should work with XAMPP. If you need to change them:
- Open `includes/config.php`
- Update the following if necessary:
  ```php
  define('DB_HOST', 'localhost');
  define('DB_USER', 'root');
  define('DB_PASS', ''); // Leave empty for default XAMPP
  define('DB_NAME', 'shebamiles_ems_new');
  ```

#### 5. Access the System
- Open your web browser
- Go to: `http://localhost/shebamiles-ems`
- You will be redirected to the login page

## Default Login Credentials

### Admin Account
- **Username**: `admin`
- **Password**: `admin123`

**IMPORTANT**: Change this password after first login for security!

## Usage Guide

### For Administrators

#### Managing Employees
1. Click "Employees" in the sidebar
2. Click "Add Employee" button
3. Fill in all required fields
4. Click "Add Employee" to save

#### Managing Departments
1. Click "Departments" in the sidebar
2. Click "Add Department" button
3. Enter department name and description
4. Click "Add Department" to save

#### Marking Attendance
1. Click "Attendance" in the sidebar
2. Select the date
3. Click "Mark Attendance" button
4. Select employee, set times and status
5. Click "Mark Attendance" to save

#### Managing Leave Requests
1. Click "Leave Requests" in the sidebar
2. View all pending requests
3. Click the review icon (checklist)
4. Approve or reject with optional comments

### For Employees

#### Requesting Leave
1. Click "Leave Requests" in the sidebar
2. Click "Request Leave" button
3. Fill in leave details
4. Submit for approval

#### Viewing Your Information
1. Access the dashboard to see your stats
2. View your attendance records
3. Track your leave requests

## File Structure

```
shebamiles-ems/
├── css/
│   └── style.css              # Main stylesheet
├── js/
├── php/
│   └── logout.php             # Logout handler
├── includes/
│   ├── config.php             # Database configuration
│   ├── auth.php               # Authentication functions
│   ├── sidebar.php            # Sidebar component
│   └── badge.php              # Floating brand badge
├── database/
│   └── shebamiles_db.sql      # Database schema
├── assets/
│   └── images/
├── login.php                  # Login page
├── dashboard.php              # Main dashboard
├── employees.php              # Employee management
├── departments.php            # Department management
├── attendance.php             # Attendance tracking
├── leaves.php                 # Leave management
├── payroll.php                # Payroll management
├── performance.php            # Performance reviews
├── users.php                  # User management
├── employee-details.php       # Employee profile view
└── README.md                  # This file
```

## Database Schema

### Main Tables
- **users**: User accounts and authentication
- **employees**: Employee personal and professional information
- **departments**: Company departments
- **attendance**: Daily attendance records
- **leave_requests**: Leave applications and approvals
- **payroll**: Salary and payment records
- **performance_reviews**: Employee performance data
- **notifications**: System notifications

## Security Features

- Password hashing using PHP `password_hash()`
- Prepared statements to prevent SQL injection
- Session management for user authentication
- Role-based access control
- Input sanitization
- CSRF token support (can be enabled)

## Troubleshooting

### Can't access the login page?
- Make sure Apache is running in XAMPP
- Check the URL: `http://localhost/shebamiles-ems`
- Verify files are in the correct htdocs folder

### Database connection error?
- Ensure MySQL is running in XAMPP
- Check database credentials in `includes/config.php`
- Verify database was created successfully in phpMyAdmin

### Login not working?
- Use default credentials: `admin` / `admin123`
- Clear browser cache and cookies
- Check database has the users table with admin user

### Pages look broken?
- Clear browser cache
- Check if CSS file is loading (F12 Developer Tools > Network tab)
- Verify `css/style.css` file exists

## System Requirements

- **PHP**: 7.4 or higher
- **MySQL**: 5.7 or higher
- **Web Server**: Apache 2.4 or higher
- **Browser**: Modern browser (Chrome, Firefox, Safari, Edge)
- **RAM**: Minimum 2GB
- **Storage**: 50MB free space

## Customization

### Changing Colors
Edit `css/style.css` and modify the CSS variables:
```css
:root {
    --primary-orange: #FF6B35;
    --primary-orange-dark: #E55A2B;
    /* ... other colors ... */
}
```

### Adding New Features
1. Create new PHP file in root directory
2. Include authentication: `require_once 'includes/auth.php';`
3. Use database connection: `$db = new Database();`
4. Add menu item in `includes/sidebar.php`

## Support & Credits

**Developed for**: IFT 301 Group Project  
**Company**: Shebamiles  
**Theme**: Orange & White  
**Version**: 1.0.0  
**Date**: February 2026  

## License

This project is created for educational purposes as part of IFT 301 coursework.

## Additional Notes

### Creating New Users
Only administrators can create new employee accounts. Each employee account includes:
- User login credentials
- Personal information
- Employment details
- Department assignment
- Salary information

### Backup Recommendations
- Regular database backups via phpMyAdmin
- Export database monthly
- Keep backup of configuration files

### Future Enhancements
The system is designed to be extensible. Potential additions:
- Payroll processing automation
- Performance review workflows
- Document management
- Email notifications
- Reports generation (PDF/Excel)
- Advanced analytics
- Mobile app integration

## Need Help?

If you encounter any issues:
1. Check the Troubleshooting section above
2. Verify all installation steps were followed
3. Check Apache and MySQL error logs in XAMPP
4. Ensure all files were extracted correctly

---

**Remember to change the default admin password after installation!**

Enjoy using Shebamiles Employee Management System! 🎉
