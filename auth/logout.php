<?php
/**
 * TaskFlow – Smart Task Management System
 * Logout Handler
 *
 * Safely destroys PHP sessions, invalidates session cookies,
 * and redirects to the login screen.
 */

declare(strict_types=1);

define('TASKFLOW_APP', true);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/auth.php';

// Accept POST with CSRF verification, or GET fallback
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_or_die();
}

logout_user();

set_flash_message('info', 'You have been signed out successfully. Have a productive day!');
redirect('auth/login.php');
