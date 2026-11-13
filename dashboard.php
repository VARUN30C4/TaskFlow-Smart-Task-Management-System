<?php
/**
 * TaskFlow – Smart Task Management System
 * Root Dashboard Redirector
 *
 * Provides a direct root entry-point mapping to tasks/index.php.
 */

declare(strict_types=1);

define('TASKFLOW_APP', true);

require_once __DIR__ . '/includes/functions.php';
redirect('tasks/index.php');

