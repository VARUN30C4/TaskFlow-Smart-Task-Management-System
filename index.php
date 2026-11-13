<?php
/**
 * TaskFlow – Smart Task Management System
 * Landing / Home Page Router
 *
 * Redirects authenticated users to their dashboard or presents
 * a modern overview of the application and its features.
 */

declare(strict_types=1);

define('TASKFLOW_APP', true);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/auth.php';

// If user is already authenticated, redirect straight to their task dashboard
if (is_logged_in()) {
    redirect('tasks/index.php');
}

$pageTitle = 'TaskFlow – Smart Task Management System';
require_once __DIR__ . '/includes/header.php';
?>

<div class="row align-items-center py-5">
    <div class="col-lg-6 mb-4 mb-lg-0">
        <span class="badge bg-primary-subtle text-primary fw-bold px-3 py-2 rounded-pill mb-3">
            <i class="bi bi-award-fill me-1"></i> RTIH Amaravati Foundation Internship Assignment
        </span>
        <h1 class="display-4 fw-extrabold text-dark tracking-tight mb-3">
            Organize & Track Your Work with <span class="text-primary">TaskFlow</span>
        </h1>
        <p class="lead text-secondary mb-4">
            A production-ready, highly secure task management system built using modern <strong>Core PHP 8+</strong>, <strong>MySQL PDO</strong>, and <strong>Bootstrap 5</strong>. Featuring multi-tenant isolation, CSRF protection, and real-time status tracking.
        </p>

        <div class="d-flex flex-wrap gap-3 mb-4">
            <a href="<?= e(base_url('auth/login.php')); ?>" class="btn btn-primary btn-lg rounded-pill px-4 shadow-sm">
                <i class="bi bi-box-arrow-in-right me-2"></i> Sign In to Workspace
            </a>
            <a href="<?= e(base_url('auth/register.php')); ?>" class="btn btn-outline-secondary btn-lg rounded-pill px-4">
                <i class="bi bi-person-plus me-2"></i> Register New Account
            </a>
        </div>

        <div class="card bg-light border-0 rounded-4 p-3 shadow-sm">
            <div class="d-flex align-items-center mb-1">
                <i class="bi bi-shield-check text-success fs-5 me-2"></i>
                <span class="fw-bold small text-dark">Pre-configured Demo Account</span>
            </div>
            <div class="small text-muted">
                Email: <code>john.doe@example.com</code> &nbsp;|&nbsp; Password: <code>Password123!</code>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="row g-3">
            <!-- Feature Card 1 -->
            <div class="col-sm-6">
                <div class="card card-custom p-4 h-100">
                    <div class="stat-icon-wrapper stat-icon-total mb-3">
                        <i class="bi bi-database-check"></i>
                    </div>
                    <h5 class="fw-bold text-dark">Level 1: Full CRUD</h5>
                    <p class="small text-secondary mb-0">
                        Create, view, edit, and delete tasks with instant feedback and delete confirmations.
                    </p>
                </div>
            </div>

            <!-- Feature Card 2 -->
            <div class="col-sm-6">
                <div class="card card-custom p-4 h-100">
                    <div class="stat-icon-wrapper stat-icon-progress mb-3">
                        <i class="bi bi-search"></i>
                    </div>
                    <h5 class="fw-bold text-dark">Level 2: Search & Filters</h5>
                    <p class="small text-secondary mb-0">
                        Search tasks by title, filter by Pending, In Progress, or Completed status with combined criteria.
                    </p>
                </div>
            </div>

            <!-- Feature Card 3 -->
            <div class="col-sm-6">
                <div class="card card-custom p-4 h-100">
                    <div class="stat-icon-wrapper stat-icon-completed mb-3">
                        <i class="bi bi-shield-lock-fill"></i>
                    </div>
                    <h5 class="fw-bold text-dark">Level 3: Security & Auth</h5>
                    <p class="small text-secondary mb-0">
                        User authentication, BCRYPT password hashing, session regeneration, and strict IDOR multi-tenant isolation.
                    </p>
                </div>
            </div>

            <!-- Feature Card 4 -->
            <div class="col-sm-6">
                <div class="card card-custom p-4 h-100">
                    <div class="stat-icon-wrapper stat-icon-pending mb-3">
                        <i class="bi bi-lightning-charge-fill"></i>
                    </div>
                    <h5 class="fw-bold text-dark">Bonus: Metrics & Pages</h5>
                    <p class="small text-secondary mb-0">
                        Real-time dashboard summary metrics, responsive pagination, and polished Bootstrap 5 UI.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

