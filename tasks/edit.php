<?php
/**
 * TaskFlow – Smart Task Management System
 * Edit Task Form & Action Handler
 *
 * Implements Level 1 Edit Task feature, Level 2 Input Validation,
 * and Level 3 Backend Authorization & User Ownership Enforcement.
 */

declare(strict_types=1);

define('TASKFLOW_APP', true);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/auth.php';

// Protect page
require_auth();

$userId = current_user_id();
$pdo = getDBConnection();

// 1. Retrieve and Validate Task ID
$taskId = filter_var($_GET['id'] ?? ($_POST['id'] ?? null), FILTER_VALIDATE_INT);

if ($taskId === false || $taskId === null || $taskId <= 0) {
    set_flash_message('danger', 'Invalid task ID provided.');
    redirect('tasks/index.php');
}

// 2. Strict Backend Authorization: Ensure task exists AND belongs to the logged-in user
$fetchStmt = $pdo->prepare('SELECT id, user_id, title, description, status, created_at FROM tasks WHERE id = :id AND user_id = :user_id LIMIT 1');
$fetchStmt->execute([
    ':id'      => $taskId,
    ':user_id' => $userId,
]);
$task = $fetchStmt->fetch();

if (!$task) {
    // Prevent IDOR: User A cannot view or edit User B's task
    set_flash_message('danger', 'Task not found or you do not have permission to modify this task.');
    redirect('tasks/index.php');
}

$errors = [];
$formData = [
    'title'       => $task['title'],
    'description' => $task['description'] ?? '',
    'status'      => $task['status'],
];

// 3. Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF Token
    verify_csrf_or_die();

    // Sanitize input
    $title       = sanitize_input($_POST['title'] ?? '');
    $description = sanitize_input($_POST['description'] ?? '');
    $status      = sanitize_input($_POST['status'] ?? '');

    $formData['title']       = $title;
    $formData['description'] = $description;
    $formData['status']      = $status;

    // Validation
    if ($title === '') {
        $errors['title'] = 'Task title is mandatory and cannot be left blank.';
    } elseif (mb_strlen($title) > 255) {
        $errors['title'] = 'Task title cannot exceed 255 characters.';
    }

    if (mb_strlen($description) > 5000) {
        $errors['description'] = 'Task description cannot exceed 5000 characters.';
    }

    $validStatuses = ['Pending', 'In Progress', 'Completed'];
    if (!in_array($status, $validStatuses, true)) {
        $errors['status'] = 'Please choose a valid task status.';
    }

    // Save Changes if Validation Passes
    if (empty($errors)) {
        $updateStmt = $pdo->prepare('
            UPDATE tasks 
            SET title = :title, 
                description = :description, 
                status = :status 
            WHERE id = :id AND user_id = :user_id
        ');

        $success = $updateStmt->execute([
            ':title'       => $title,
            ':description' => ($description !== '' ? $description : null),
            ':status'      => $status,
            ':id'          => $taskId,
            ':user_id'     => $userId,
        ]);

        if ($success) {
            set_flash_message('success', 'Task "' . $title . '" was updated successfully!');
            redirect('tasks/index.php');
        } else {
            $errors['general'] = 'Failed to update the task. Please try again.';
        }
    }
}

$pageTitle = 'Edit Task – TaskFlow';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-7">
        <!-- Breadcrumbs -->
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb small">
                <li class="breadcrumb-item"><a href="<?= e(base_url('tasks/index.php')); ?>" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit Task #<?= (int)$task['id']; ?></li>
            </ol>
        </nav>

        <div class="card card-custom p-4 p-md-5 mb-4 shadow-sm border-0">
            <div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom">
                <div class="d-flex align-items-center">
                    <div class="d-inline-flex p-3 bg-primary-subtle text-primary rounded-circle me-3">
                        <i class="bi bi-pencil-square fs-3"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold text-dark mb-1">Edit Task</h3>
                        <p class="text-muted small mb-0">Update task information, description, or change status</p>
                    </div>
                </div>
                <div class="text-end">
                    <span class="badge bg-light text-dark border px-3 py-2 rounded-pill small">
                        Task #<?= (int)$task['id']; ?>
                    </span>
                </div>
            </div>

            <?php if (!empty($errors['general'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle-fill me-2"></i><?= e($errors['general']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form action="<?= e(base_url('tasks/edit.php?id=' . (int)$task['id'])); ?>" method="POST" novalidate class="needs-validation">
                <?= csrf_field(); ?>
                <input type="hidden" name="id" value="<?= (int)$task['id']; ?>">

                <!-- Task Title -->
                <div class="mb-3">
                    <label for="title" class="form-label fw-semibold small text-secondary">Task Title <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control form-control-lg <?= isset($errors['title']) ? 'is-invalid' : ''; ?>" 
                           id="title" 
                           name="title" 
                           value="<?= e($formData['title']); ?>" 
                           maxlength="255" 
                           required 
                           autofocus>
                    <?php if (isset($errors['title'])): ?>
                        <div class="invalid-feedback"><?= e($errors['title']); ?></div>
                    <?php else: ?>
                        <div class="invalid-feedback">Task title cannot be empty.</div>
                    <?php endif; ?>
                </div>

                <!-- Description -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <label for="description" class="form-label fw-semibold small text-secondary">Description</label>
                        <span class="small text-muted" id="descriptionCharCounter">0 / 5000 characters</span>
                    </div>
                    <textarea class="form-control <?= isset($errors['description']) ? 'is-invalid' : ''; ?>" 
                              id="description" 
                              name="description" 
                              rows="5" 
                              maxlength="5000"><?= e($formData['description']); ?></textarea>
                    <?php if (isset($errors['description'])): ?>
                        <div class="invalid-feedback"><?= e($errors['description']); ?></div>
                    <?php endif; ?>
                </div>

                <!-- Status Selection -->
                <div class="mb-4">
                    <label for="status" class="form-label fw-semibold small text-secondary">Task Status <span class="text-danger">*</span></label>
                    <select class="form-select <?= isset($errors['status']) ? 'is-invalid' : ''; ?>" id="status" name="status" required>
                        <option value="Pending" <?= ($formData['status'] === 'Pending') ? 'selected' : ''; ?>>⏳ Pending</option>
                        <option value="In Progress" <?= ($formData['status'] === 'In Progress') ? 'selected' : ''; ?>>🔄 In Progress</option>
                        <option value="Completed" <?= ($formData['status'] === 'Completed') ? 'selected' : ''; ?>>✅ Completed</option>
                    </select>
                    <?php if (isset($errors['status'])): ?>
                        <div class="invalid-feedback"><?= e($errors['status']); ?></div>
                    <?php endif; ?>
                </div>

                <!-- Meta Information -->
                <div class="p-3 bg-light rounded-3 border mb-4 small text-muted d-flex justify-content-between align-items-center">
                    <div>
                        <i class="bi bi-calendar3 me-1"></i> Created on: <strong><?= format_datetime($task['created_at']); ?></strong>
                    </div>
                    <div>
                        Current Status: <?= get_status_badge($task['status']); ?>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                    <a href="<?= e(base_url('tasks/index.php')); ?>" class="btn btn-outline-secondary rounded-pill px-4">
                        <i class="bi bi-x-circle me-1"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">
                        <i class="bi bi-save me-1"></i> Update Task
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

