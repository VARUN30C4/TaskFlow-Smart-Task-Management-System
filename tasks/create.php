<?php
/**
 * TaskFlow – Smart Task Management System
 * Create Task Form & Action Handler
 *
 * Implements Level 1 Add Task feature, Level 2 Input Validation,
 * and Level 3 User Ownership & Prepared Statements.
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
$errors = [];
$formData = [
    'title'       => '',
    'description' => '',
    'status'      => 'Pending',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Verify CSRF Token
    verify_csrf_or_die();

    // 2. Collect and sanitize input
    $title       = sanitize_input($_POST['title'] ?? '');
    $description = sanitize_input($_POST['description'] ?? '');
    $status      = sanitize_input($_POST['status'] ?? 'Pending');

    $formData['title']       = $title;
    $formData['description'] = $description;
    $formData['status']      = $status;

    // 3. Validation
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
        $errors['status'] = 'Invalid status selected.';
    }

    // 4. Save Task if Validation Passes
    if (empty($errors)) {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare('
            INSERT INTO tasks (user_id, title, description, status, created_at) 
            VALUES (:user_id, :title, :description, :status, NOW())
        ');

        $success = $stmt->execute([
            ':user_id'     => $userId,
            ':title'       => $title,
            ':description' => ($description !== '' ? $description : null),
            ':status'      => $status,
        ]);

        if ($success) {
            set_flash_message('success', 'Task "' . $title . '" was created successfully!');
            redirect('tasks/index.php');
        } else {
            $errors['general'] = 'Failed to save the task. Please try again.';
        }
    }
}

$pageTitle = 'Add New Task – TaskFlow';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-7">
        <!-- Breadcrumb navigation -->
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb small">
                <li class="breadcrumb-item"><a href="<?= e(base_url('tasks/index.php')); ?>" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Add New Task</li>
            </ol>
        </nav>

        <div class="card card-custom p-4 p-md-5 mb-4 shadow-sm border-0">
            <div class="d-flex align-items-center mb-4 pb-2 border-bottom">
                <div class="d-inline-flex p-3 bg-primary-subtle text-primary rounded-circle me-3">
                    <i class="bi bi-plus-square-fill fs-3"></i>
                </div>
                <div>
                    <h3 class="fw-bold text-dark mb-1">Create New Task</h3>
                    <p class="text-muted small mb-0">Fill in the details below to add a task to your workspace</p>
                </div>
            </div>

            <?php if (!empty($errors['general'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle-fill me-2"></i><?= e($errors['general']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form action="<?= e(base_url('tasks/create.php')); ?>" method="POST" novalidate class="needs-validation">
                <?= csrf_field(); ?>

                <!-- Task Title -->
                <div class="mb-3">
                    <label for="title" class="form-label fw-semibold small text-secondary">Task Title <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control form-control-lg <?= isset($errors['title']) ? 'is-invalid' : ''; ?>" 
                           id="title" 
                           name="title" 
                           value="<?= e($formData['title']); ?>" 
                           placeholder="e.g. Prepare client review presentation" 
                           maxlength="255" 
                           required 
                           autofocus>
                    <?php if (isset($errors['title'])): ?>
                        <div class="invalid-feedback"><?= e($errors['title']); ?></div>
                    <?php else: ?>
                        <div class="invalid-feedback">Task title is mandatory and cannot be empty.</div>
                    <?php endif; ?>
                    <div class="form-text small text-muted">A clear, concise summary of the goal.</div>
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
                              placeholder="Provide any additional context, specifications, or checklist items..." 
                              maxlength="5000"><?= e($formData['description']); ?></textarea>
                    <?php if (isset($errors['description'])): ?>
                        <div class="invalid-feedback"><?= e($errors['description']); ?></div>
                    <?php endif; ?>
                </div>

                <!-- Status Selection -->
                <div class="mb-4">
                    <label for="status" class="form-label fw-semibold small text-secondary">Initial Status</label>
                    <select class="form-select <?= isset($errors['status']) ? 'is-invalid' : ''; ?>" id="status" name="status">
                        <option value="Pending" <?= ($formData['status'] === 'Pending') ? 'selected' : ''; ?>>⏳ Pending</option>
                        <option value="In Progress" <?= ($formData['status'] === 'In Progress') ? 'selected' : ''; ?>>🔄 In Progress</option>
                        <option value="Completed" <?= ($formData['status'] === 'Completed') ? 'selected' : ''; ?>>✅ Completed</option>
                    </select>
                    <?php if (isset($errors['status'])): ?>
                        <div class="invalid-feedback"><?= e($errors['status']); ?></div>
                    <?php endif; ?>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                    <a href="<?= e(base_url('tasks/index.php')); ?>" class="btn btn-outline-secondary rounded-pill px-4">
                        <i class="bi bi-x-circle me-1"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">
                        <i class="bi bi-check2-circle me-1"></i> Save Task
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

