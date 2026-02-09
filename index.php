<?php
// INDEX PAGE / HOME PAGE
// PURPOSE: Redirect landing page that directs users to login
// WORKFLOW: Page load → Check for active session in auth → Redirect to login if not authenticated
// This file is the entry point for the application - all requests come through here first

// Redirect to login page (handled by auth functions if needed)
// If user is already logged in, auth middleware will redirect to dashboard
header('Location: login.php');
exit();
?>
