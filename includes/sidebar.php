<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$user = getCurrentUser();
?>
<aside class="sidebar">
    <div class="sidebar-header">
        <a href="dashboard.php" class="sidebar-brand">
            Shebamiles
            <small>EMS</small>
        </a>
    </div>
    
    <ul class="sidebar-menu">
        <!-- Dashboard (All Users) -->
        <li>
            <a href="dashboard.php" class="<?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <!-- Employees (All Users, but limited view for employees) -->
        <?php if (hasPermission('view_employees')): ?>
        <li>
            <a href="employees.php" class="<?php echo $currentPage === 'employees.php' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i>
                <span>Employees</span>
            </a>
        </li>
        <?php endif; ?>

        <!-- Departments (All Users, but view-only for employees) -->
        <?php if (hasPermission('view_departments')): ?>
        <li>
            <a href="departments.php" class="<?php echo $currentPage === 'departments.php' ? 'active' : ''; ?>">
                <i class="fas fa-building"></i>
                <span>Departments</span>
            </a>
        </li>
        <?php endif; ?>

        <!-- Attendance (All Users, but view-only own attendance for employees) -->
        <?php if (hasPermission('view_attendance')): ?>
        <li>
            <a href="attendance.php" class="<?php echo $currentPage === 'attendance.php' ? 'active' : ''; ?>">
                <i class="fas fa-clock"></i>
                <span>Attendance</span>
            </a>
        </li>
        <?php endif; ?>

        <!-- Leave Requests (All Users, but own requests only for employees) -->
        <?php if (hasPermission('view_leaves')): ?>
        <li>
            <a href="leaves.php" class="<?php echo $currentPage === 'leaves.php' ? 'active' : ''; ?>">
                <i class="fas fa-calendar-alt"></i>
                <span>Leave Requests</span>
            </a>
        </li>
        <?php endif; ?>

        <!-- Documents (All Users) -->
        <?php if (hasPermission('view_documents')): ?>
        <li>
            <a href="documents.php" class="<?php echo $currentPage === 'documents.php' ? 'active' : ''; ?>">
                <i class="fas fa-file"></i>
                <span>Documents</span>
            </a>
        </li>
        <?php endif; ?>

        <!-- Announcements (All Users) -->
        <li>
            <a href="announcements.php" class="<?php echo $currentPage === 'announcements.php' ? 'active' : ''; ?>">
                <i class="fas fa-bullhorn"></i>
                <span>Announcements</span>
            </a>
        </li>

        <!-- Holiday Calendar (All Users) -->
        <?php if (hasPermission('view_holidays')): ?>
        <li>
            <a href="holiday-calendar.php" class="<?php echo $currentPage === 'holiday-calendar.php' ? 'active' : ''; ?>">
                <i class="fas fa-calendar-check"></i>
                <span>Holiday Calendar</span>
            </a>
        </li>
        <?php endif; ?>

        <!-- ADMIN-ONLY SECTION -->
        <?php if (hasRole('admin')): ?>
        <li style="border-top: 1px solid rgba(255,255,255,0.1); margin: 10px 0; padding-top: 10px;">
            <span style="font-size: 0.75rem; color: rgba(255,255,255,0.6); text-transform: uppercase; font-weight: 700; padding: 0 1rem; display: block; margin-bottom: 0.5rem;">
                🔐 Admin Panel
            </span>
        </li>

        <!-- Payroll (Admin Only) -->
        <?php if (hasPermission('view_payroll')): ?>
        <li>
            <a href="payroll.php" class="<?php echo $currentPage === 'payroll.php' ? 'active' : ''; ?>">
                <i class="fas fa-money-bill-wave"></i>
                <span>Payroll</span>
            </a>
        </li>
        <?php endif; ?>

        <!-- Performance (Admin Only) -->
        <?php if (hasPermission('view_performance')): ?>
        <li>
            <a href="performance.php" class="<?php echo $currentPage === 'performance.php' ? 'active' : ''; ?>">
                <i class="fas fa-chart-line"></i>
                <span>Performance</span>
            </a>
        </li>
        <?php endif; ?>

        <!-- User Management (Admin Only) -->
        <?php if (hasPermission('view_users')): ?>
        <li>
            <a href="users.php" class="<?php echo $currentPage === 'users.php' ? 'active' : ''; ?>">
                <i class="fas fa-user-shield"></i>
                <span>User Management</span>
            </a>
        </li>
        <?php endif; ?>

        <!-- Settings (Admin Only) -->
        <?php if (hasPermission('view_settings')): ?>
        <li>
            <a href="settings.php" class="<?php echo $currentPage === 'settings.php' ? 'active' : ''; ?>">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </a>
        </li>
        <?php endif; ?>

        <!-- Activity Log (Admin Only) -->
        <?php if (hasPermission('view_activity_log')): ?>
        <li>
            <a href="activity-log.php" class="<?php echo $currentPage === 'activity-log.php' ? 'active' : ''; ?>">
                <i class="fas fa-history"></i>
                <span>Activity Log</span>
            </a>
        </li>
        <?php endif; ?>
        <?php endif; ?>
    </ul>
    
    <div class="sidebar-user">
        <div class="sidebar-user-avatar">
            <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
        </div>
        <div class="sidebar-user-info">
            <h4><?php echo htmlspecialchars($user['full_name']); ?></h4>
            <p><?php echo getRoleDisplayName($user['role']); ?></p>
            <a href="profile.php" style="font-size: 0.75rem; color: rgba(255,255,255,0.8); text-decoration: none; margin-top: 0.25rem; display: inline-block;">
                <i class="fas fa-user-circle"></i> View Profile
            </a>
        </div>
    </div>
</aside>
