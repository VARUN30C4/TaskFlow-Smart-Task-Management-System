<?php
/**
 * TaskFlow – Smart Task Management System
 * Quick Status Update Action Handler
 *
 * Implements Level 2 Task Status Management with Level 3 Backend Authorization.
 */

declare(strict_types=1);

define('TASKFLOW_APP', true);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/auth.php';

// Protect action
require_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    redirect('tasks/index.php');
}

// 1. Verify CSRF Token
verify_csrf_or_die();

// 2. Validate Task ID & Status
$taskId = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
$status = sanitize_input($_POST['status'] ?? '');
$userId = current_user_id();

$validStatuses = ['Pending', 'In Progress', 'Completed'];

if ($taskId === false || $taskId === null || $taskId <= 0 || !in_array($status, $validStatuses, true)) {
    set_flash_message('danger', 'Invalid task status update request.');
    redirect('tasks/index.php');
}

// 3. Strict Backend Authorization: Update only if task belongs to current user
$pdo = getDBConnection();
$updateStmt = $pdo->prepare('UPDATE tasks SET status = :status WHERE id = :id AND user_id = :user_id');
$updateStmt->execute([
    ':status'  => $status,
    ':id'      => $taskId,
    ':user_id' => $userId,
]);

if ($updateStmt->rowCount() > 0) {
    set_flash_message('success', 'Task status changed to "' . $status . '".');
} else {
    // If rowCount is 0, status was already the same or task was unauthorized
    set_flash_message('info', 'No changes made, or task is not accessible.');
}

redirect('tasks/index.php');
