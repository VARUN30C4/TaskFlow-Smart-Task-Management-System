<?php
/**
 * TaskFlow – Smart Task Management System
 * Global Header Template Partial
 */

declare(strict_types=1);

if (!defined('TASKFLOW_APP')) {
    define('TASKFLOW_APP', true);
}

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/auth.php';

$pageTitle = $pageTitle ?? 'TaskFlow – Smart Task Management System';
$currentUser = current_user();
$flash = get_flash_message();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle); ?></title>

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= e(base_url('assets/css/style.css')); ?>">
</head>
<body>

<!-- Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-white bg-white border-bottom sticky-top py-2">
    <div class="container">
        <!-- Brand Logo -->
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?= e(base_url(is_logged_in() ? 'tasks/index.php' : 'index.php')); ?>">
            <img src="<?= e(base_url('assets/images/logo.svg')); ?>" alt="TaskFlow Logo" width="36" height="36">
            <span class="fs-4 fw-bold text-dark">Task<span class="text-primary">Flow</span></span>
        </a>

        <!-- Mobile Hamburger Toggle -->
        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#taskflowNavbar" aria-controls="taskflowNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navbar Links -->
        <div class="collapse navbar-collapse" id="taskflowNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 ps-lg-3">
                <?php if (is_logged_in()): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= (str_contains($_SERVER['SCRIPT_NAME'] ?? '', 'tasks/index.php')) ? 'active fw-semibold text-primary' : ''; ?>" href="<?= e(base_url('tasks/index.php')); ?>">
                            <i class="bi bi-grid-fill me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= (str_contains($_SERVER['SCRIPT_NAME'] ?? '', 'tasks/create.php')) ? 'active fw-semibold text-primary' : ''; ?>" href="<?= e(base_url('tasks/create.php')); ?>">
                            <i class="bi bi-plus-circle me-1"></i> Add Task
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= e(base_url('index.php')); ?>">
                            <i class="bi bi-house-door me-1"></i> Home
                        </a>
                    </li>
                <?php endif; ?>
            </ul>

            <!-- Right-side User Actions -->
            <div class="d-flex align-items-center gap-2">
                <?php if (is_logged_in()): ?>
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle d-flex align-items-center gap-2 rounded-pill px-3 py-1" type="button" id="userMenuDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="badge bg-primary rounded-circle p-2 text-white"><i class="bi bi-person-fill"></i></span>
                            <span class="d-none d-sm-inline fw-medium text-dark"><?= e($currentUser['full_name']); ?></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 mt-2" aria-labelledby="userMenuDropdown">
                            <li class="px-3 py-2 border-bottom">
                                <div class="fw-semibold text-dark"><?= e($currentUser['full_name']); ?></div>
                                <div class="small text-muted text-truncate" style="max-width: 200px;"><?= e($currentUser['email']); ?></div>
                            </li>
                            <li>
                                <a class="dropdown-item py-2" href="<?= e(base_url('tasks/index.php')); ?>">
                                    <i class="bi bi-card-checklist me-2 text-primary"></i> My Tasks
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item py-2" href="<?= e(base_url('tasks/create.php')); ?>">
                                    <i class="bi bi-plus-square me-2 text-success"></i> Create Task
                                </a>
                            </li>
                            <li><hr class="dropdown-divider my-1"></li>
                            <li>
                                <form action="<?= e(base_url('auth/logout.php')); ?>" method="POST" class="m-0">
                                    <?= csrf_field(); ?>
                                    <button type="submit" class="dropdown-item py-2 text-danger">
                                        <i class="bi bi-box-arrow-right me-2"></i> Sign Out
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="<?= e(base_url('auth/login.php')); ?>" class="btn btn-outline-primary rounded-pill px-3 py-1">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
                    </a>
                    <a href="<?= e(base_url('auth/register.php')); ?>" class="btn btn-primary rounded-pill px-3 py-1">
                        <i class="bi bi-person-plus me-1"></i> Register
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<!-- Main Container -->
<main class="py-4">
    <div class="container">
        <!-- Flash Messages Display -->
        <?php if ($flash): ?>
            <div class="alert alert-<?= e($flash['type']); ?> alert-dismissible fade show shadow-sm border-0 alert-auto-dismiss mb-4" role="alert">
                <div class="d-flex align-items-center">
                    <?php if ($flash['type'] === 'success'): ?>
                        <i class="bi bi-check-circle-fill fs-5 me-2 text-success"></i>
                    <?php elseif ($flash['type'] === 'danger'): ?>
                        <i class="bi bi-exclamation-octagon-fill fs-5 me-2 text-danger"></i>
                    <?php elseif ($flash['type'] === 'warning'): ?>
                        <i class="bi bi-exclamation-triangle-fill fs-5 me-2 text-warning"></i>
                    <?php else: ?>
                        <i class="bi bi-info-circle-fill fs-5 me-2 text-info"></i>
                    <?php endif; ?>
                    <div><?= e($flash['message']); ?></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
