<?php
/**
 * TaskFlow – Smart Task Management System
 * Root Logout Redirector
 *
 * Provides a direct root entry-point mapping to auth/logout.php.
 */

declare(strict_types=1);

define('TASKFLOW_APP', true);

require_once __DIR__ . '/includes/functions.php';
redirect('auth/logout.php');
