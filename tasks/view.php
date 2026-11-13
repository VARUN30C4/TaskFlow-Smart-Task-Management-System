<?php
/**
 * TaskFlow – Smart Task Management System
 * View Task Details Page
 *
 * Implements Level 1 View Task Details feature with Level 3 Backend Authorization.
 */

declare(strict_types=1);

define('TASKFLOW_APP', true);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/auth.php';

// Enforce authentication
require_auth();

$userId = current_user_id();
$pdo = getDBConnection();

// Retrieve and validate Task ID
$taskId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);

if ($taskId === false || $taskId === null || $taskId <= 0) {
    set_flash_message('danger', 'Invalid task identifier provided.');
    redirect('tasks/index.php');
}

// Strict backend authorization: ensure task belongs to the authenticated user
$stmt = $pdo->prepare('
    SELECT id, user_id, title, description, status, created_at 
    FROM tasks 
    WHERE id = :id AND user_id = :user_id 
    LIMIT 1
');
$stmt->execute([
    ':id'      => $taskId,
    ':user_id' => $userId,
]);
$task = $stmt->fetch();

if (!$task) {
    set_flash_message('danger', 'Task not found or you do not have permission to view it.');
    redirect('tasks/index.php');
}

$pageTitle = 'Task Details #' . (int)$task['id'] . ' – TaskFlow';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-7">
        <!-- Breadcrumb Navigation -->
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb small">
                <li class="breadcrumb-item"><a href="<?= e(base_url('tasks/index.php')); ?>" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Task #<?= (int)$task['id']; ?></li>
            </ol>
        </nav>

        <div class="card card-custom p-4 p-md-5 mb-4 shadow-sm border-0">
            <!-- Header section -->
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4 pb-3 border-bottom">
                <div class="d-flex align-items-center">
                    <div class="d-inline-flex p-3 bg-primary-subtle text-primary rounded-circle me-3">
                        <i class="bi bi-card-text fs-3"></i>
                    </div>
                    <div>
                        <span class="badge bg-light text-secondary border rounded-pill mb-1">Task #<?= (int)$task['id']; ?></span>
                        <h3 class="fw-bold text-dark mb-0"><?= e($task['title']); ?></h3>
                    </div>
                </div>
                <div>
                    <?= get_status_badge($task['status']); ?>
                </div>
            </div>

            <!-- Description Body -->
            <div class="mb-4">
                <h6 class="fw-bold text-secondary text-uppercase small tracking-wide mb-2">Description</h6>
                <div class="p-3 bg-light rounded-3 border">
                    <?php if (!empty($task['description'])): ?>
                        <div class="text-dark lh-base" style="white-space: pre-wrap; word-break: break-word;"><?= e($task['description']); ?></div>
                    <?php else: ?>
                        <span class="text-muted fst-italic">No additional description provided for this task.</span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Meta Information -->
            <div class="row g-3 mb-4">
                <div class="col-sm-6">
                    <div class="p-3 bg-white border rounded-3">
                        <span class="text-muted small d-block mb-1"><i class="bi bi-calendar3 me-1"></i> Date Created</span>
                        <strong class="text-dark"><?= format_datetime($task['created_at']); ?></strong>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="p-3 bg-white border rounded-3">
                        <span class="text-muted small d-block mb-1"><i class="bi bi-shield-check me-1"></i> Ownership Status</span>
                        <strong class="text-success"><i class="bi bi-check-circle-fill me-1"></i> Verified Owner</strong>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 pt-3 border-top">
                <a href="<?= e(base_url('tasks/index.php')); ?>" class="btn btn-outline-secondary rounded-pill px-4">
                    <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
                </a>
                <div class="d-flex gap-2">
                    <a href="<?= e(base_url('tasks/edit.php?id=' . (int)$task['id'])); ?>" class="btn btn-primary rounded-pill px-4">
                        <i class="bi bi-pencil-square me-1"></i> Edit Task
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

