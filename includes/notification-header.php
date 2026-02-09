<?php
// Notification header component - shows bell icon with unread count
require_once 'helpers.php';

if (isset($_SESSION['user_id'])) {
    try {
        $unread_count = getUnreadNotificationCount($_SESSION['user_id']);
    } catch (Exception $e) {
        $unread_count = 0;
    }
}
?>
<div class="notification-header">
    <a href="notifications.php" class="notification-bell">
        <i class="fas fa-bell"></i>
        <?php if (isset($unread_count) && $unread_count > 0): ?>
            <span class="notification-badge"><?php echo $unread_count > 99 ? '99+' : $unread_count; ?></span>
        <?php endif; ?>
    </a>
</div>
