<?php
/**
 * TaskFlow – Smart Task Management System
 * Delete Task Action Handler
 *
 * Implements Level 1 Delete Task feature, Level 2 ID validation,
 * Level 3 Backend Authorization & User Ownership Protection, and CSRF defense.
 */

declare(strict_types=1);

define('TASKFLOW_APP', true);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/auth.php';

// Protect action
require_auth();

// Only allow state-changing operations via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    set_flash_message('danger', 'Method Not Allowed. Tasks must be deleted using secure POST requests.');
    redirect('tasks/index.php');
}

// 1. Verify CSRF Token
verify_csrf_or_die();

// 2. Validate Task ID
$taskId = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
$userId = current_user_id();

if ($taskId === false || $taskId === null || $taskId <= 0) {
    set_flash_message('danger', 'Invalid task identifier provided.');
    redirect('tasks/index.php');
}

// 3. Strict Backend Authorization: Delete only if task belongs to current user
$pdo = getDBConnection();
$deleteStmt = $pdo->prepare('DELETE FROM tasks WHERE id = :id AND user_id = :user_id');
$deleteStmt->execute([
    ':id'      => $taskId,
    ':user_id' => $userId,
]);

if ($deleteStmt->rowCount() > 0) {
    set_flash_message('success', 'Task #' . $taskId . ' was deleted successfully.');
} else {
    // If rowCount is 0, the task was either non-existent or belonged to another user account
    set_flash_message('danger', 'Task not found or you do not have permission to delete this task.');
}

redirect('tasks/index.php');
