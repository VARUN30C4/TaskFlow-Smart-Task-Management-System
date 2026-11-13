<?php
/**
 * TaskFlow – Smart Task Management System
 * Root Registration Redirector
 *
 * Provides a direct root entry-point mapping to auth/register.php.
 */

declare(strict_types=1);

define('TASKFLOW_APP', true);

require_once __DIR__ . '/includes/functions.php';
redirect('auth/register.php');
