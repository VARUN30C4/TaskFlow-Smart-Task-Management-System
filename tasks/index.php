<?php
/**
 * TaskFlow – Smart Task Management System
 * Main Task Dashboard
 *
 * Implements Level 1 CRUD display, Level 2 Search & Status Filtering,
 * Level 3 Multi-Tenant Isolation & Authorization, and Bonus Pagination.
 */

declare(strict_types=1);

define('TASKFLOW_APP', true);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/auth.php';

// Ensure user is authenticated
require_auth();

$userId = current_user_id();
$currentUser = current_user();
$pdo = getDBConnection();

// 1. Fetch Dashboard Statistics strictly for the logged-in user
$statsStmt = $pdo->prepare("
    SELECT 
        COUNT(*) AS total,
        COALESCE(SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END), 0) AS pending,
        COALESCE(SUM(CASE WHEN status = 'In Progress' THEN 1 ELSE 0 END), 0) AS in_progress,
        COALESCE(SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END), 0) AS completed
    FROM tasks 
    WHERE user_id = :user_id
");
$statsStmt->execute([':user_id' => $userId]);
$stats = $statsStmt->fetch() ?: ['total' => 0, 'pending' => 0, 'in_progress' => 0, 'completed' => 0];

// 2. Handle Search and Status Filters
$search = sanitize_input($_GET['search'] ?? '');
$statusFilter = sanitize_input($_GET['status'] ?? '');
$validStatuses = ['Pending', 'In Progress', 'Completed'];

$whereConditions = ['user_id = :user_id'];
$queryParams = [':user_id' => $userId];

if ($search !== '') {
    $whereConditions[] = 'title LIKE :search';
    $queryParams[':search'] = '%' . $search . '%';
}

if ($statusFilter !== '' && in_array($statusFilter, $validStatuses, true)) {
    $whereConditions[] = 'status = :status';
    $queryParams[':status'] = $statusFilter;
}

$whereSql = implode(' AND ', $whereConditions);

// 3. Pagination Setup
$perPage = 6;
$currentPage = max(1, (int)($_GET['page'] ?? 1));

// Count total matching tasks
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE {$whereSql}");
foreach ($queryParams as $key => $val) {
    $countStmt->bindValue($key, $val);
}
$countStmt->execute();
$totalMatching = (int)$countStmt->fetchColumn();

$totalPages = max(1, (int)ceil($totalMatching / $perPage));
if ($currentPage > $totalPages) {
    $currentPage = $totalPages;
}
$offset = ($currentPage - 1) * $perPage;

// 4. Fetch Paginated Tasks for Current User
$tasksStmt = $pdo->prepare("
    SELECT id, user_id, title, description, status, created_at 
    FROM tasks 
    WHERE {$whereSql} 
    ORDER BY created_at DESC 
    LIMIT :limit OFFSET :offset
");

foreach ($queryParams as $key => $val) {
    $tasksStmt->bindValue($key, $val);
}
$tasksStmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$tasksStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$tasksStmt->execute();
$tasks = $tasksStmt->fetchAll();

$isFiltered = ($search !== '' || $statusFilter !== '');

$pageTitle = 'Task Dashboard – TaskFlow';
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Welcome Banner -->
<div class="hero-banner p-4 p-md-5 mb-4">
    <div class="row align-items-center">
        <div class="col-lg-8">
            <span class="badge bg-white text-primary mb-2 px-3 py-1 fw-bold rounded-pill shadow-sm">
                <i class="bi bi-stars me-1"></i> RTIH IT Internship Project
            </span>
            <h2 class="fw-bold mb-1">Hello, <?= e($currentUser['full_name']); ?>! 👋</h2>
            <p class="mb-0 opacity-90">
                Track, organize, and accomplish your daily tasks with real-time status management.
            </p>
        </div>
        <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
            <button type="button" class="btn btn-light btn-lg rounded-pill fw-semibold shadow-sm text-primary" data-bs-toggle="modal" data-bs-target="#createTaskModal">
                <i class="bi bi-plus-lg me-1"></i> Add New Task
            </button>
        </div>
    </div>
</div>

<!-- Dashboard Statistics Cards (Calculated strictly for logged-in user) -->
<div class="row g-3 mb-4">
    <!-- Total Tasks -->
    <div class="col-6 col-md-3">
        <a href="<?= e(base_url('tasks/index.php')); ?>" class="text-decoration-none text-dark">
            <div class="stat-card p-3 p-md-4 shadow-sm h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-uppercase tracking-wide">Total Tasks</span>
                    <div class="stat-icon-wrapper stat-icon-total">
                        <i class="bi bi-collection-fill"></i>
                    </div>
                </div>
                <h2 class="fw-bold mb-0 text-dark"><?= (int)$stats['total']; ?></h2>
                <span class="small text-muted">All active tasks</span>
            </div>
        </a>
    </div>

    <!-- Pending Tasks -->
    <div class="col-6 col-md-3">
        <a href="<?= e(base_url('tasks/index.php?status=Pending')); ?>" class="text-decoration-none text-dark">
            <div class="stat-card p-3 p-md-4 shadow-sm h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-uppercase tracking-wide">Pending</span>
                    <div class="stat-icon-wrapper stat-icon-pending">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                </div>
                <h2 class="fw-bold mb-0 text-dark"><?= (int)$stats['pending']; ?></h2>
                <span class="small text-muted">Awaiting start</span>
            </div>
        </a>
    </div>

    <!-- In Progress Tasks -->
    <div class="col-6 col-md-3">
        <a href="<?= e(base_url('tasks/index.php?status=In+Progress')); ?>" class="text-decoration-none text-dark">
            <div class="stat-card p-3 p-md-4 shadow-sm h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-uppercase tracking-wide">In Progress</span>
                    <div class="stat-icon-wrapper stat-icon-progress">
                        <i class="bi bi-arrow-repeat"></i>
                    </div>
                </div>
                <h2 class="fw-bold mb-0 text-dark"><?= (int)$stats['in_progress']; ?></h2>
                <span class="small text-muted">Currently active</span>
            </div>
        </a>
    </div>

    <!-- Completed Tasks -->
    <div class="col-6 col-md-3">
        <a href="<?= e(base_url('tasks/index.php?status=Completed')); ?>" class="text-decoration-none text-dark">
            <div class="stat-card p-3 p-md-4 shadow-sm h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-uppercase tracking-wide">Completed</span>
                    <div class="stat-icon-wrapper stat-icon-completed">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                </div>
                <h2 class="fw-bold mb-0 text-dark"><?= (int)$stats['completed']; ?></h2>
                <span class="small text-muted">Finished goals</span>
            </div>
        </a>
    </div>
</div>

<!-- Search & Status Filter Toolbar -->
<div class="card card-custom p-3 mb-4 shadow-sm border-0">
    <form action="<?= e(base_url('tasks/index.php')); ?>" method="GET" class="row g-2 align-items-center">
        <!-- Search Input -->
        <div class="col-md-5 col-lg-6">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0 text-muted">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" 
                       class="form-control border-start-0 ps-0" 
                       name="search" 
                       placeholder="Search tasks by title..." 
                       value="<?= e($search); ?>" 
                       autocomplete="off">
                <?php if ($search !== ''): ?>
                    <a href="<?= e(base_url('tasks/index.php' . ($statusFilter ? '?status=' . urlencode($statusFilter) : ''))); ?>" class="btn btn-outline-secondary border-start-0 border-end" title="Clear Search">
                        <i class="bi bi-x"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Status Filter Dropdown -->
        <div class="col-md-4 col-lg-3">
            <select name="status" class="form-select" onchange="this.form.submit()">
                <option value="">All Tasks (Filter Status)</option>
                <option value="Pending" <?= ($statusFilter === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                <option value="In Progress" <?= ($statusFilter === 'In Progress') ? 'selected' : ''; ?>>In Progress</option>
                <option value="Completed" <?= ($statusFilter === 'Completed') ? 'selected' : ''; ?>>Completed</option>
            </select>
        </div>

        <!-- Action Buttons -->
        <div class="col-md-3 col-lg-3 d-flex gap-2 justify-content-md-end">
            <button type="submit" class="btn btn-primary px-3 rounded-pill">
                <i class="bi bi-funnel-fill me-1"></i> Filter
            </button>
            <?php if ($isFiltered): ?>
                <a href="<?= e(base_url('tasks/index.php')); ?>" class="btn btn-outline-secondary px-3 rounded-pill" title="Reset all filters">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Task List Table -->
<div class="card card-custom shadow-sm border-0 overflow-hidden mb-4">
    <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
        <h5 class="fw-bold mb-0 text-dark">
            <i class="bi bi-list-task me-2 text-primary"></i>
            Tasks 
            <span class="badge bg-light text-dark border ms-2 fw-normal small">
                Showing <?= count($tasks); ?> of <?= $totalMatching; ?>
            </span>
        </h5>
        <div class="d-flex align-items-center gap-2">
            <a href="<?= e(base_url('tasks/create.php')); ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                <i class="bi bi-plus-circle me-1"></i> Dedicated Form
            </a>
        </div>
    </div>

    <?php if (empty($tasks)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">
                <i class="bi bi-inbox"></i>
            </div>
            <h4 class="fw-bold text-dark">No Tasks Found</h4>
            <?php if ($isFiltered): ?>
                <p class="text-muted mb-3">No tasks match your search or filter criteria.</p>
                <a href="<?= e(base_url('tasks/index.php')); ?>" class="btn btn-outline-primary rounded-pill px-4">
                    Clear Filter Criteria
                </a>
            <?php else: ?>
                <p class="text-muted mb-3">You don't have any tasks in your list yet. Start by adding your first task!</p>
                <button type="button" class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#createTaskModal">
                    <i class="bi bi-plus-lg me-1"></i> Create Your First Task
                </button>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-custom table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width: 5%;">ID</th>
                        <th style="width: 25%;">Task Title</th>
                        <th style="width: 35%;">Description</th>
                        <th style="width: 15%;">Status</th>
                        <th style="width: 10%;">Created</th>
                        <th style="width: 10%;" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tasks as $task): ?>
                        <tr>
                            <!-- Task ID -->
                            <td class="text-muted small fw-semibold">#<?= (int)$task['id']; ?></td>

                            <!-- Task Title -->
                            <td>
                                <div class="fw-bold text-dark"><?= e($task['title']); ?></div>
                            </td>

                            <!-- Description -->
                            <td>
                                <div class="text-secondary small text-break">
                                    <?php if (!empty($task['description'])): ?>
                                        <?= nl2br(e($task['description'])); ?>
                                    <?php else: ?>
                                        <span class="text-muted fst-italic">No description provided</span>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <!-- Status Badge -->
                            <td>
                                <button type="button" 
                                        class="btn p-0 border-0 bg-transparent" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#statusModal" 
                                        data-task-id="<?= (int)$task['id']; ?>" 
                                        data-task-title="<?= e($task['title']); ?>" 
                                        data-current-status="<?= e($task['status']); ?>" 
                                        title="Click to quickly update status">
                                    <?= get_status_badge($task['status']); ?>
                                </button>
                            </td>

                            <!-- Created Date -->
                            <td class="text-muted small text-nowrap">
                                <?= format_datetime($task['created_at']); ?>
                            </td>

                            <!-- Action Buttons -->
                            <td class="text-end text-nowrap">
                                <div class="btn-group btn-group-sm" role="group">
                                    <!-- Edit Button -->
                                    <a href="<?= e(base_url('tasks/edit.php?id=' . (int)$task['id'])); ?>" 
                                       class="btn btn-outline-secondary" 
                                       data-bs-toggle="tooltip" 
                                       title="Edit Task">
                                        <i class="bi bi-pencil-square text-primary"></i>
                                    </a>

                                    <!-- Delete Button (Trigger confirmation modal) -->
                                    <button type="button" 
                                            class="btn btn-outline-secondary" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#deleteTaskModal" 
                                            data-task-id="<?= (int)$task['id']; ?>" 
                                            data-task-title="<?= e($task['title']); ?>" 
                                            title="Delete Task">
                                        <i class="bi bi-trash text-danger"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination Controls -->
        <?php if ($totalPages > 1): ?>
            <div class="card-footer bg-white py-3 px-4 border-top d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
                <span class="small text-muted">
                    Page <strong><?= $currentPage; ?></strong> of <strong><?= $totalPages; ?></strong>
                </span>

                <nav aria-label="Task table pagination">
                    <ul class="pagination pagination-sm mb-0">
                        <!-- First Page -->
                        <li class="page-item <?= ($currentPage <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="<?= e(base_url('tasks/index.php?' . http_build_query(array_merge($_GET, ['page' => 1])))); ?>" aria-label="First">
                                <span aria-hidden="true">&laquo;&laquo;</span>
                            </a>
                        </li>

                        <!-- Previous Page -->
                        <li class="page-item <?= ($currentPage <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="<?= e(base_url('tasks/index.php?' . http_build_query(array_merge($_GET, ['page' => $currentPage - 1])))); ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>

                        <!-- Page Numbers -->
                        <?php 
                        $startPage = max(1, $currentPage - 2);
                        $endPage = min($totalPages, $currentPage + 2);
                        for ($p = $startPage; $p <= $endPage; $p++): 
                        ?>
                            <li class="page-item <?= ($p === $currentPage) ? 'active' : ''; ?>">
                                <a class="page-link" href="<?= e(base_url('tasks/index.php?' . http_build_query(array_merge($_GET, ['page' => $p])))); ?>">
                                    <?= $p; ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <!-- Next Page -->
                        <li class="page-item <?= ($currentPage >= $totalPages) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="<?= e(base_url('tasks/index.php?' . http_build_query(array_merge($_GET, ['page' => $currentPage + 1])))); ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>

                        <!-- Last Page -->
                        <li class="page-item <?= ($currentPage >= $totalPages) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="<?= e(base_url('tasks/index.php?' . http_build_query(array_merge($_GET, ['page' => $totalPages])))); ?>" aria-label="Last">
                                <span aria-hidden="true">&raquo;&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- ================================================================= -->
<!-- MODAL 1: Create Task Modal (Quick Add from Dashboard) -->
<!-- ================================================================= -->
<div class="modal fade" id="createTaskModal" tabindex="-1" aria-labelledby="createTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom py-3">
                <h5 class="modal-title fw-bold" id="createTaskModalLabel">
                    <i class="bi bi-plus-circle text-primary me-2"></i>Create New Task
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= e(base_url('tasks/create.php')); ?>" method="POST" novalidate class="needs-validation">
                <div class="modal-body p-4">
                    <?= csrf_field(); ?>

                    <!-- Task Title -->
                    <div class="mb-3">
                        <label for="modal_title" class="form-label fw-semibold small text-secondary">Task Title <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control" 
                               id="modal_title" 
                               name="title" 
                               placeholder="e.g. Implement prepared statements" 
                               maxlength="255" 
                               required>
                        <div class="invalid-feedback">Task title is mandatory and cannot be empty.</div>
                    </div>

                    <!-- Task Description -->
                    <div class="mb-3">
                        <label for="modal_description" class="form-label fw-semibold small text-secondary">Description (Optional)</label>
                        <textarea class="form-control" 
                                  id="modal_description" 
                                  name="description" 
                                  rows="3" 
                                  placeholder="Provide relevant details or sub-steps for this task..." 
                                  maxlength="5000"></textarea>
                    </div>

                    <!-- Initial Status -->
                    <div class="mb-2">
                        <label for="modal_status" class="form-label fw-semibold small text-secondary">Initial Status</label>
                        <select class="form-select" id="modal_status" name="status">
                            <option value="Pending" selected>Pending</option>
                            <option value="In Progress">In Progress</option>
                            <option value="Completed">Completed</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top py-3">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">
                        <i class="bi bi-check-lg me-1"></i> Save Task
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ================================================================= -->
<!-- MODAL 2: Quick Status Change Modal -->
<!-- ================================================================= -->
<div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom py-3">
                <h6 class="modal-title fw-bold" id="statusModalLabel">
                    <i class="bi bi-arrow-repeat text-info me-2"></i>Update Status
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= e(base_url('tasks/update_status.php')); ?>" method="POST">
                <div class="modal-body p-3 text-center">
                    <?= csrf_field(); ?>
                    <input type="hidden" name="id" id="status_task_id" value="">
                    <p class="small text-muted mb-2 text-truncate" id="status_task_title"></p>
                    <select class="form-select mb-2" name="status" id="new_status_select" required>
                        <option value="Pending">Pending</option>
                        <option value="In Progress">In Progress</option>
                        <option value="Completed">Completed</option>
                    </select>
                </div>
                <div class="modal-footer border-top py-2 d-flex justify-content-between">
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ================================================================= -->
<!-- MODAL 3: Delete Task Confirmation Modal (Requirement: confirm before delete) -->
<!-- ================================================================= -->
<div class="modal fade" id="deleteTaskModal" tabindex="-1" aria-labelledby="deleteTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom py-3">
                <h5 class="modal-title fw-bold text-danger" id="deleteTaskModalLabel">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Confirm Task Deletion
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= e(base_url('tasks/delete.php')); ?>" method="POST">
                <div class="modal-body p-4">
                    <?= csrf_field(); ?>
                    <input type="hidden" name="id" id="delete_task_id" value="">
                    <p class="text-secondary mb-2">
                        Are you sure you want to permanently delete this task?
                    </p>
                    <div class="p-3 bg-light rounded-3 border mb-3">
                        <strong class="text-dark" id="delete_task_title"></strong>
                    </div>
                    <p class="small text-danger mb-0">
                        <i class="bi bi-info-circle me-1"></i>This action cannot be undone.
                    </p>
                </div>
                <div class="modal-footer border-top py-3">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4">
                        <i class="bi bi-trash-fill me-1"></i> Yes, Delete Task
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

