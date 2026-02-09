# Shebamiles EMS - Feature Documentation

## Core Features Overview

The Shebamiles Employee Management System provides comprehensive HR management tools organized into functional modules. Each module handles specific business processes with role-based access controls.

---

## 1. Authentication & Access Control

### Overview
Manages user accounts, login security, and role-based access throughout the system.

### Key Components

#### Login System (`login.php`)
**Purpose**: Authenticate users and create secure sessions

**User Authentication Flow**:
```
User enters credentials
    ↓
Form posts to login.php
    ↓
Sanitize inputs
    ↓
Call login() function
    ├─ Query users with JOIN to employees
    ├─ Verify password with password_verify()
    └─ Set session variables
    ↓
Create session
    ↓
Redirect to dashboard.php
```

**Security Features**:
- Passwords hashed with bcrypt (never stored in plain text)
- Only active users can login
- Database errors don't expose details
- Input sanitization before validation

#### Session Management
**Session Variables Set at Login**:
- `user_id`: Database user ID
- `username`: Login username
- `email`: User email address
- `role`: Access level (admin, manager, employee)
- `employee_id`: Associated employee record (if exists)
- `full_name`: User's full name for display

**Session Timeout**:
- PHP default: 24 minutes (configurable in php.ini)
- Automatic redirect to login on timeout
- Last login timestamp updated for audit

#### Authentication Functions

| Function | Purpose | Access |
|----------|---------|--------|
| `isLoggedIn()` | Check if user has valid session | All pages |
| `hasRole()` | Verify user role | All pages |
| `requireLogin()` | Middleware - block non-authenticated | All pages |
| `requireAdmin()` | Middleware - block non-admins | Admin pages |
| `hasPermission()` | Check specific permission | All pages |

### Role-Based Access Control

**Three User Roles**:

#### Admin Role
- Full system access
- Manage all users, employees, departments
- Approve/reject all requests
- Configure system settings
- View all reports and analytics
- Manage payroll and performance

#### Manager Role
- Employee management within department
- Approve/reject leave requests in team
- View team attendance
- Create performance reviews
- Limited reporting access
- Cannot access user management or settings

#### Employee Role
- View own profile and employment records
- Request leave
- View own attendance history
- Check own payroll information
- View company announcements
- Cannot access other employee data

**Permission Matrix**:
```
Feature              | Admin | Manager | Employee
---------------------|-------|---------|----------
View Dashboard       | Yes   | Yes     | Limited
Manage Employees     | Yes   | Own Dept | No
Approve Leaves       | Yes   | Yes     | No
Mark Attendance      | Yes   | Yes     | Own Records
View Analytics       | Yes   | Limited | No
Manage Settings      | Yes   | No      | No
Manage Users         | Yes   | No      | No
View All Payroll     | Yes   | Limited | Own Records
```

---

## 2. Employee Management

### Overview
Complete employee lifecycle management including hiring, records updates, and termination.

### Core Pages & Functions

#### Employee List (`employees.php`)
**Displays**: Table of all current employees with filtering/search

**Data Shown**:
- Employee ID
- Name
- Email
- Department
- Position
- Hire Date
- Status (active/inactive)
- Contact phone

**Features**:
- Search by name, email, or ID
- Filter by department
- Filter by status (active/inactive)
- Sort by any column
- Pagination for large datasets

**Database Query**:
```sql
SELECT e.*, d.department_name 
FROM employees e
LEFT JOIN departments d ON e.department_id = d.department_id
WHERE e.status = 'active' 
ORDER BY e.first_name, e.last_name
```

#### Employee Details (`employee-details.php`)
**Displays**: Complete employee profile and related information

**Information Sections**:

1. **Personal Information**
   - Name, email, phone
   - Date of birth, gender
   - Address, city, state, country

2. **Employment Details**
   - Employee ID
   - Department
   - Position
   - Hire date and tenure
   - Salary

3. **Contact & Emergency**
   - Primary phone
   - Emergency contact name
   - Emergency phone
   - Relationship to employee

4. **Related Records**
   - Attendance history (last 30 days)
   - Leave requests
   - Performance reviews
   - Payroll records
   - Documents

**Permissions**:
- Admins: View/edit all employees
- Managers: View own department employees
- Employees: View own profile only

#### Employee Creation/Editing
**Data Collected**:
- Basic info: First name, last name, email, phone
- Employment: Department, position, hire date
- Compensation: Base salary
- Personal: DOB, gender, address details
- Emergency: Contact name and phone

**Validation**:
- Email must be unique and valid format
- Phone number format validation
- Salary must be positive
- Hire date cannot be in future
- Required fields enforcement

**On Create**:
1. Insert into employees table
2. Optionally create associated user account
3. Log activity: "Created employee: John Doe"

**On Update**:
1. Validate all inputs
2. Check for email uniqueness
3. Update employee record
4. Log activity: "Updated employee: {changes}"

---

## 3. Department Management

### Overview
Organizational structure configuration and department operations.

### Department List (`departments.php`)

**Displays**:
- Department name
- Description
- Manager (if assigned)
- Employee count
- Budget (if set)

**Admin Functions**:
- Add new department
- Edit department name/description
- Assign department manager
- Set budget
- View team members

**Database Relationships**:
```
1 Department : Many Employees (1:N)
1 Department : 1 Manager (1:1)
```

**Common Operations**:

#### Get department with statistics
```php
$stmt = $conn->prepare(
    "SELECT 
        d.*,
        u.username as manager_name,
        COUNT(e.employee_id) as employee_count
    FROM departments d
    LEFT JOIN users u ON d.manager_id = u.user_id
    LEFT JOIN employees e ON d.department_id = e.department_id
    GROUP BY d.department_id"
);
```

#### Assign manager to department
```php
$stmt = $conn->prepare(
    "UPDATE departments 
     SET manager_id = ? 
     WHERE department_id = ?"
);
$stmt->execute([$managerId, $deptId]);
```

---

## 4. Attendance Management

### Overview
Daily attendance tracking, status recording, and attendance-based reports.

### Attendance Marking (`attendance.php`)

**Status Types**:
- **Present**: Employee worked full day
- **Absent**: Employee did not come to work
- **Leave**: Employee is on approved leave
- **Half-Day**: Employee worked partial day

**Marking Attendance**:

**Who Can Mark**:
- Admins: Mark attendance for any employee
- Managers: Mark attendance for their department
- Employees: (auto-marked based on system settings)

**Mark Workflow**:
1. Select date
2. Select employee(s)
3. Choose status
4. Enter check-in/check-out times (optional)
5. Add notes (optional)
6. Submit

**Database Constraints**:
- Unique constraint on (employee_id, date)
- Prevents duplicate entries for same day
- Can be updated if already marked

**Attendance Report Query**:
```sql
SELECT 
    DATE(date) as attendance_date,
    status,
    COUNT(*) as count
FROM attendance
WHERE date BETWEEN ? AND ?
GROUP BY DATE(date), status
```

#### Attendance Summary by Employee
**Metric Calculations**:
- Total working days = calendar days - weekends - holidays
- Present days = count where status = 'present'
- Absent days = count where status = 'absent'
- Leave days = count where status = 'leave'
- Attendance percentage = (present / working_days) * 100

**Example Calculation**:
```
Month: February 2026
Total days: 28
Weekends: 8
Holidays: 1
Working days: 19

Employee A:
- Present: 15 days
- Absent: 2 days
- Leave: 2 days
- Attendance %: (15/19) * 100 = 78.95%
```

### Leave Auto-Marking
When leave request is approved:
1. Get start_date and end_date from leave_requests
2. Loop each day in range
3. Skip weekends
4. Skip company holidays
5. Insert attendance record with status='leave'

---

## 5. Leave Management

### Overview
Employee leave request submission, manager approval, and leave balance tracking.

### Leave Request Workflow

**Step 1: Employee Submits Request** (`leaves.php`)
```
Employee visits leaves.php
    ↓
Clicks "Request Leave"
    ↓
Fills form:
  - Leave Type (vacation, sick, personal, unpaid)
  - Start Date
  - End Date
  - Reason
    ↓
Submit
    ↓
Request inserted with status='pending'
```

**Step 2: Manager Reviews**
```
Manager sees pending requests
    ↓
Reviews reason and dates
    ↓
Approves:
  - Set status='approved'
  - Set approved_by=manager_id
  - Auto-mark attendance as 'leave'
  ↓
Rejects:
  - Set status='rejected'
  - Add rejection reason
```

**Leave Request Data Structure**:
```
leave_id          - Primary key
employee_id       - Who requested
leave_type        - Category of leave
start_date        - First day off
end_date          - Last day off
days_requested    - Number of days
reason            - Why taking leave
status            - pending/approved/rejected/cancelled
approved_by       - Manager who approved
approval_notes    - Approval comments
created_at        - When requested
updated_at        - Last modified
```

#### Leave Types

**Vacation**:
- Planned time off
- Usually requires advance notice
- Deducted from annual leave balance

**Sick Leave**:
- Employee illness
- May require medical certificate
- Separate balance from vacation

**Personal Leave**:
- Personal reasons
- Usually limited days per year
- Not paid in some companies

**Unpaid Leave**:
- No salary for this period
- Requires special approval
- Doesn't affect leave balance

#### Leave Balance Calculation

**Method**: Manual tracking in database

**Formula**:
```
Annual Leave Granted = Setting value (e.g., 20 days)
Period Start = getSetting('leave_year_start')

Approved This Year = Count approved requests in current year

Remaining Balance = Annual Grant - Approved Used

Note: Pending or rejected requests don't count
```

**Query**:
```php
$stmt = $conn->prepare(
    "SELECT 
        COUNT(CASE WHEN status='approved' THEN 1 END) as used_days,
        COUNT(CASE WHEN status='pending' THEN 1 END) as pending_days
    FROM leave_requests
    WHERE employee_id = ?
      AND YEAR(start_date) = YEAR(CURDATE())
      AND leave_type = 'vacation'"
);
```

---

## 6. Payroll Management

### Overview
Salary calculations, payment processing, and payroll reports.

### Payroll Processing

**Input Data**:
- Base salary (from employee records)
- Bonuses (if any)
- Deductions (insurance, loans, etc.)
- Taxes (calculated based on rules)

**Payroll Calculation** (`payroll.php`):

```
Base Salary: $3,000
+ Bonus: $200
= Gross: $3,200
- Deductions: $150
- Taxes: $400
= Net Pay: $2,650
```

**Pay Period**:
- Typically monthly
- Defined by pay_period_start and pay_period_end
- Can be customized per company

**Payment Method Options**:
- Bank transfer
- Check
- Cash

**Payroll Status Tracking**:
- **Pending**: Created but not processed
- **Processed**: Ready to pay
- **Paid**: Payment completed

**Admin Functions** (payroll.php):
1. Generate payroll for period
2. Review salary calculations
3. Adjust bonuses/deductions
4. Mark as processed
5. Record payment date and method
6. Generate payroll reports

**Employee View**:
- See own payroll records
- Download pay stubs
- View payment history
- Check deductions and taxes

---

## 7. Performance Management

### Overview
Employee performance reviews and evaluation tracking.

### Performance Review Workflow (`performance.php`)

**Review Cycle**:
- Annual or bi-annual reviews
- Manager -> Employee evaluation
- Set performance goals
- Document feedback

**Review Creation**:
```
Manager initiates review
    ↓
Sets review period
    ↓
Fills review form:
  - Rating (1.0 to 5.0)
  - Feedback
  - Strengths
  - Areas for improvement
  - Goals for next period
    ↓
Submit
    ↓
Employee notified
    ↓
Employee acknowledges
```

**Rating Scale**:
```
1.0-1.9: Unsatisfactory
  - Not meeting job requirements
  - Needs immediate improvement
  - May require action plan

2.0-2.9: Needs Improvement
  - Below expected level
  - Should improve specific areas
  - May impact advancement

3.0-3.9: Meets Expectations
  - Performing job adequately
  - Meeting all requirements
  - Satisfactory performance

4.0-4.9: Exceeds Expectations
  - Goes above and beyond
  - Additional achievements
  - Leadership qualities
  - Promotion candidate

5.0: Outstanding
  - Exceptional performance
  - Exemplary work
  - Top performer
```

**Review History**:
- View all reviews for specific employee
- Track rating trends over time
- Compare to previous reviews
- Calculate average rating

---

## 8. System Administration

### User Management (`users.php`)

**Admin-Only Function**: Create and manage user accounts

**User Creation**:
1. Set username (must be unique)
2. Set email (must be unique)
3. Set temporary password (auto-generated)
4. Assign role (admin, manager, employee)
5. Link to employee record (optional)
6. Set status (active/inactive)

**Password Management**:
- Users cannot set own password initially
- Admin generates temporary password
- User must change password on first login
- Or admin can reset password anytime

**User Statuses**:
- **Active**: Can login and use system
- **Inactive**: Locked out of system

**User Deletion**:
- Cannot delete (data integrity)
- Only set to inactive status

### Settings Management (`settings.php`)

**Admin-Only Configuration**:

**Company Settings**:
- Company name
- Company logo/branding
- Financial year start date
- Time zone

**HR Settings**:
- Working days per week
- Leave year start date
- Annual leave days
- Overtime policy

**System Settings**:
- Email notifications enabled
- Notification email address
- Session timeout
- Database backups

**Notification Settings**:
- Leave request notifications
- Attendance notifications
- Payroll ready notifications
- Performance review notifications

---

## 9. Notifications & Announcements

### Notifications (`notifications.php`)

**Type**: User-specific alerts about system events

**Generated Events**:
- Leave request submitted
- Leave request approved/rejected
- Performance review due
- Payroll processed
- Attendance marked
- Documents uploaded
- System updates

**Notification Features**:
- Mark as read/unread
- Delete notifications
- Follow link to related record
- Filter by type (info, warning, success, error)

**Notifications Table**:
```
notification_id
user_id
title
message
type (info, warning, success, error)
is_read (boolean)
action_url (link to page)
created_at
```

### Announcements (`announcements.php`)

**Type**: Company-wide messages visible to all users

**Features**:
- Post by admins
- Optional expiry date
- View count tracking
- Markdown/HTML formatting

**Announcement Fields**:
```
announcement_id
title
content
author_id (admin who posted)
created_at
updated_at
expiry_date
is_active (bool)
views (counter)
```

---

## 10. Document Management

### Overview
Store and manage employee documents (contracts, certifications, etc.)

### Document Features (`documents.php`)

**Supported Document Types**:
- PDF
- Word (.doc, .docx)
- Excel (.xls, .xlsx)
- Images (.jpg, .png)
- Text files

**Upload Process**:
1. Select file
2. Associate with employee
3. Add title and description
4. Upload
5. File stored on server

**Security**:
- Validate file type
- Scan for viruses (if configured)
- Run access controls (who can view)
- Version control support

**Document Metadata**:
```
document_id
employee_id
title
file_path
file_type
file_size
uploaded_by
description
created_at
```

---

## 11. Holiday Calendar

### Overview
Manage company holidays and non-working days

### Holiday Management (`holiday-calendar.php`)

**Features**:
- Add holidays by date
- Name and description
- Mark as paid/unpaid holiday
- View calendar view
- Search holidays

**Holiday Usage**:
- Exclude from working day calculations
- Prevent leave requests on holidays
- Auto-exclude from attendance requirements

**Database**:
```
holiday_id
holiday_name
holiday_date
description
is_paid (boolean)
created_at
updated_at
```

---

## 12. Activity Logging & Audit Trail

### Overview
Track all system actions for compliance and security

### Activity Log (`activity-log.php`)

**Tracked Actions**:
- User login/logout
- Employee records created/updated/deleted
- Attendance marked
- Leave approved/rejected
- Payroll processed
- Documents uploaded
- Settings changed
- Users created/modified

**Logged Information**:
```
log_id
user_id (who did it)
action (create, update, delete, view)
entity_type (employee, attendance, etc.)
entity_id (which record)
description (what changed)
ip_address (security)
user_agent (browser info)
created_at (when)
```

**Admin Features**:
- View all activity
- Filter by user
- Filter by action type
- Filter by date range
- Export activity logs

**Security Benefits**:
- Detect unauthorized access
- Track data changes
- Compliance audits
- Investigate issues

---

## 13. Dashboard & Analytics

### Overview
Executive dashboard with key metrics and insights

### Dashboard (`dashboard.php`)

**Admin Dashboard Shows**:
- Total employees
- Total departments
- Today's attendance (present, absent, leave)
- Pending leave requests
- Recent activities
- Department distribution (chart)
- Attendance trends (graph)
- Leave request statistics
- Recent employee additions

**Employee Dashboard Shows**:
- Own profile summary
- Leave balance
- Recent attendance
- Upcoming holidays
- Announcements
- Own performance reviews

**Analytics Available**:
- Attendance rate by employee
- Attendance rate by department
- Leave usage by type and employee
- Payroll summary
- Performance rating distribution
- Turnover metrics (if supported)

---

## Cross-Cutting Features

### Search & Filter
**Available On**:
- Employee list
- Attendance records
- Leave requests
- Payroll records
- Activity logs
- Documents

**Search Criteria**:
- Name
- Email
- ID
- Department
- Date range
- Status

### Pagination
**Applied To**:
- Any list with 10+ records
- Default 20-50 items per page
- Configurable page size
- Previous/next navigation
- Jump to page

### Export/Print
**Supported For**:
- Employee lists
- Attendance reports
- Payroll records
- Activity logs
- Performance reviews

**Formats**:
- HTML (print-friendly)
- CSV (for Excel)
- PDF (if configured)

### Notifications
**Triggered By**:
- System events
- Manual actions
- Scheduled tasks

**Delivery**:
- In-app notifications
- Email notifications (optional)
- Dashboard alerts

---

**Last Updated**: February 2026  
**Feature Version**: 1.0
