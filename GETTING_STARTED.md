# Shebamiles EMS - Getting Started Guide

## Quick Start (New Users)

### For End Users (Employees)

#### Login
1. Go to `login.php`
2. Enter your username and password
3. Click "Login"
4. You'll be redirected to your dashboard

#### Navigate Your Dashboard
- **Top Navigation**: Shows your name and notifications
- **Left Sidebar**: Quicklinks to your main functions
- **Main Content**: Your personalized information

#### Common Tasks

**View My Profile**
1. Click "Profile" in sidebar
2. See your personal and employment information
3. Update contact details (if allowed)

**Check Attendance**
1. Click "Attendance" → "My Attendance"
2. See your check-in/check-out records
3. View attendance percentage

**Request Leave**
1. Click "Leaves" → "Request Leave"
2. Select leave type (vacation, sick, personal)
3. Choose start and end dates
4. Add reason (optional)
5. Submit - manager will review and approve/reject

**View Earning**
1. Click "Payroll"
2. See your salary, bonuses, deductions
3. Download pay stubs (if available)

**Read Announcements**
1. Click "Announcements"
2. See all company-wide messages
3. Check expiry dates

---

### For Managers

#### Additional Permissions
- Approve/reject team's leave requests
- View team members and their records
- Mark attendance for team
- Create performance reviews for team members

#### Common Tasks

**Approve Leave Request**
1. Click "Leaves" → "Pending Requests"
2. Review employee's request
3. Choose: Approve (with notes) or Reject (with reason)
4. Click Submit
5. Employee gets notification

**Mark Attendance**
1. Click "Attendance" → "Mark Attendance"
2. Select date
3. Select team members
4. Choose status (Present, Absent, Leave, Half-Day)
5. Add check-in/check-out times (optional)
6. Submit

**Create Performance Review**
1. Click "Performance" → "Add Review"
2. Select employee
3. Rate from 1.0 to 5.0
4. Add feedback, strengths, improvements
5. Set goals for next period
6. Submit

---

### For Admins

#### Full System Access
- All manager permissions
- Plus: User management, settings, all analytics
- Can view all records company-wide
- Can configure system settings

#### Common Admin Tasks

**Create New Employee**
1. Click "Employees" → "Add Employee"
2. Fill personal information
3. Select department and position
4. Set hire date and salary
5. Add emergency contact
6. Submit
7. Optionally create user account (to allow login)

**Create New User Account**
1. Click "Users" → "Add User"
2. Set username (must be unique)
3. Set email (must be unique)
4. Assign role (admin, manager, employee)
5. Link to employee record (optional)
6. Set temporary password (auto-generated)
7. Submit
8. User gets notification with credentials

**Process Payroll**
1. Click "Payroll" → "Generate Payroll"
2. Select pay period (month)
3. Review salary calculations
4. Add bonuses/deductions if needed
5. Mark as "Processed"
6. Record payment date and method
7. Submit
8. Employees get notification

**Configure System Settings**
1. Click "Settings"
2. Update:
   - Company name and contact info
   - Financial year start date
   - Working days per week
   - Leave year start date
   - Annual leave days
   - Notification preferences
3. Save changes

**View Activity Audit Trail**
1. Click "Activity Log"
2. See all system actions (who did what, when)
3. Filter by:
   - User
   - Action type (create, update, delete)
   - Date range
   - Entity type (employee, payroll, etc)
4. Export if needed for compliance

**Manage Departments**
1. Click "Departments"
2. Create new:
   - Department name
   - Description
   - Assign manager
   - Set budget
3. Edit existing department
4. View team members
5. See department statistics

**Set Company Holidays**
1. Click "Holiday Calendar"
2. Add holiday:
   - Date
   - Name
   - Description
   - Paid/Unpaid option
3. View calendar
4. Delete if needed

**Create Announcement**
1. Click "Announcements" → "Create New"
2. Title
3. Content (can use formatting)
4. Set expiry date (optional)
5. Publish
6. All employees see in announcement section

---

## File Navigation Guide

### Employee-Related Pages

**employees.php**
- View all employees
- Search and filter
- Sort by any column
- Link to detailed profiles

**employee-details.php?id=X**
- Complete employee profile
- Personal and employment information
- Emergency contacts
- Related records (attendance, payroll, reviews)
- Edit button for admins

**users.php** (Admin only)
- User accounts management
- Create accounts
- Assign roles
- Reset passwords
- Activate/deactivate accounts

### HR Operations

**attendance.php**
- Mark attendance
- View attendance records
- Generate reports
- Download attendance history

**leaves.php**
- Submit leave requests (employees)
- Review pending (managers)
- Approve/reject (managers)
- View leave history (all)
- Check leave balance

**payroll.php**
- Generate payroll records (admin)
- View salary information (employees/managers)
- Download pay stubs
- Payment history

**performance.php**
- Create performance reviews (managers/admin)
- View reviews (employees can see their own)
- Track rating trends
- Performance history

### Organization

**departments.php**
- View departments
- Create new departments
- Assign managers
- Set budgets
- View team composition

**holiday-calendar.php**
- View company holidays
- Add holidays (admin)
- Check working days calculations
- Verify leave eligibility

### Communication & Notifications

**announcements.php**
- View company-wide messages
- Create announcements (admin)
- Edit announcements (admin)
- Schedule expiry dates

**notifications.php**
- View personal notifications
- Mark as read
- Filter by type
- Delete old notifications

### System Management

**dashboard.php**
- Overview analytics
- Key metrics
- Recent activities
- Charts and graphs

**documents.php**
- Upload documents
- View documents
- Download documents
- Associate with employees

**activity-log.php**
- Audit trail of all actions
- Filter by user/action/date
- Export for compliance
- Security investigation

**settings.php** (Admin only)
- Company information
- Financial settings
- HR settings
- Notification preferences
- System configuration

**profile.php**
- View own profile
- Update personal information
- Change password
- See login history

---

## Role Comparison Matrix

### Quick Permission Reference

| Feature | Employee | Manager | Admin |
|---------|----------|---------|-------|
| **View Dashboard** | Yes (Limited) | Yes | Yes |
| **View All Employees** | Directory only | Own Dept | Yes |
| **Edit Employee Records** | Own only | Own Dept | Yes |
| **Mark Attendance** | View own | Own Dept | Yes, All |
| **Approve Leave** | No | Own Dept | Yes, All |
| **Process Payroll** | View own | Limited | Yes |
| **Create Reviews** | No | Own Dept | Yes |
| **Manage Users** | No | No | Yes |
| **System Settings** | No | No | Yes |
| **View Audit Log** | No | No | Yes |
| **Create Announcements** | No | No | Yes |
| **Manage Holidays** | No | No | Yes |

---

## Common Workflows

### New Employee Onboarding

**Day 1 (Admin)**:
1. Create employee record (employees.php)
2. Create user account (users.php)
3. Assign to department
4. Send credentials to employee

**Day 1 (Employee)**:
1. Login with provided credentials
2. Update profile (optional)
3. Review company announcements
4. Check employee handbook (if available)

**Week 1 (Manager)**:
1. Confirm employee check-in
2. Mark attendance
3. Explain leave policies
4. Assign initial tasks

### Monthly Attendance Tracking

**Throughout Month**:
- Manager marks attendance daily
- Employees view own attendance
- Holidays are auto-excluded

**End of Month**:
- Admin reviews attendance report
- Identifies absences/issues
- Prepares for payroll

### Monthly Payroll Processing

**Month Start**:
- Admin reviews salary settings
- Confirms any bonuses/deductions

**Month End**:
1. Admin navigates to payroll.php
2. Generates payroll for the month
3. Reviews calculations
4. Marks as "Processed"
5. Records payment date/method
6. Employees get notification

### Quarterly Performance Reviews

**Review Period Start**:
1. Manager creates performance_reviews
2. Rates employee (1.0-5.0)
3. Provides feedback
4. Sets goals

**Review Period End**:
1. Employee reviews feedback (performance.php)
2. Acknowledges review
3. Discusses with manager (offline)

**Annual Analysis**:
1. Admin views all reviews
2. Analyzes rating trends
3. Uses for raises/promotions
4. Exports for compliance

### Leave Request Approval

**1. Employee Submits**:
- Visits leaves.php
- Fills request form
- Submits to manager

**2. Manager Reviews**:
- Sees pending in leaves.php
- Checks dates (no conflict)
- Checks leave balance
- Approves/Rejects

**3. System Auto Actions**:
- If approved: marks attendance as 'leave' for dates
- Creates notification for employee
- Updates leave balance

---

## Troubleshooting

### "Access Denied" Message
**Cause**: Your role doesn't have permission for this page
**Solution**:
- Ask manager/admin to grant permission
- Check your role in profile.php
- Only admins can manage permissions

### "Database not found"
**Cause**: Database not initialized
**Solution**:
- Admin: Import database/shebamiles_db.sql
- Restart XAMPP MySQL
- Reload page

### Login Not Working
**Cause**: Invalid credentials or account inactive
**Solution**:
- Check CAPS LOCK
- Ask admin to reset password
- Verify account is active (not disabled)

### Notification Not Received
**Cause**: Notifications disabled or email not configured
**Solution**:
- Check notification settings (settings.php for admin)
- Check email configuration
- Verify notification type is enabled

### Leave Request Rejected?
**Cause**: Manager denied request (see reason in notification)
**Solution**:
- Resubmit at different dates
- Discuss with manager
- Check remaining leave balance

---

## Best Practices

### For Employees
1. **Keep profile updated** - Helps HR contact you
2. **Request leave in advance** - Give managers time to plan
3. **Check announcements regularly** - Important updates
4. **Review payslips** - Verify correct salary

### For Managers
1. **Mark attendance promptly** - Don't wait until end of month
2. **Review leave requests quickly** - Don't let them pile up
3. **Provide constructive feedback** - During performance reviews
4. **Keep emergency contacts current** - For employee records

### For Admins
1. **Regular backups** - Database integrity is critical
2. **Review audit logs** - Detect suspicious activity
3. **Update holidays** - Before payroll processing
4. **Archive old data** - Keeps database performance good
5. **Test features** - Before rolling out to users

---

## Security Tips

### Password Security
- Never share password
- Change default password after first login
- Use strong passwords (8+ chars, mixed case, numbers)

### Data Protection
- Don't leave computer unlocked with application open
- Logout when leaving workstation
- Don't share sensitive information via chat

### Reporting Issues
- Report unusual activity to admin
- Document suspicious behavior with timestamps
- Use audit logs for investigation

---

## Support & Help

### Issues with Specific Feature?
1. Check FEATURES.md - Detailed explanation
2. Review this guide's troubleshooting section
3. Contact your system administrator

### Need Code Explanation?
1. Check API_REFERENCE.md - Function documentation
2. Review ARCHITECTURE.md - System design
3. Check comments in source code

### Database Questions?
1. See DATABASE.md - Schema documentation
2. Review relationship diagrams
3. Check table definitions

---

**Last Updated**: February 2026  
**Guide Version**: 1.0
