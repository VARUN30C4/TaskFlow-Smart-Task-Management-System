<?php
/**
 * TaskFlow – Smart Task Management System
 * User Login Authentication Page
 *
 * Implements secure authentication using password_verify(), session fixation prevention
 * via session_regenerate_id(), and friendly user feedback.
 */

declare(strict_types=1);

define('TASKFLOW_APP', true);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/auth.php';

// Prevent logged-in users from accessing the login page
require_guest();

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Verify CSRF Token
    verify_csrf_or_die();

    // 2. Collect and sanitize input
    $email    = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // 3. Validation
    if ($email === '') {
        $errors['email'] = 'Email address is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }

    if ($password === '') {
        $errors['password'] = 'Password is required.';
    }

    // 4. Authenticate against database
    if (empty($errors)) {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare('SELECT id, full_name, email, password FROM users WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Password verification passed
            login_user($user);

            set_flash_message('success', 'Welcome back, ' . $user['full_name'] . '!');
            redirect('tasks/index.php');
        } else {
            // Generic security-conscious error message to avoid user enumeration
            $errors['general'] = 'Invalid email address or password. Please check your credentials and try again.';
        }
    }
}

$pageTitle = 'Sign In – TaskFlow';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card card-custom p-4 p-md-5 my-3">
            <div class="text-center mb-4">
                <div class="d-inline-flex p-3 bg-primary-subtle text-primary rounded-circle mb-3">
                    <i class="bi bi-box-arrow-in-right fs-2"></i>
                </div>
                <h3 class="fw-bold text-dark mb-1">Welcome Back</h3>
                <p class="text-muted small">Sign in to manage and track your tasks</p>
            </div>

            <?php if (!empty($errors['general'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-octagon-fill me-2"></i><?= e($errors['general']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form action="<?= e(base_url('auth/login.php')); ?>" method="POST" novalidate class="needs-validation">
                <?= csrf_field(); ?>

                <!-- Email -->
                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold small text-secondary">Email Address <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted"><i class="bi bi-envelope"></i></span>
                        <input type="email" 
                               class="form-control <?= isset($errors['email']) ? 'is-invalid' : ''; ?>" 
                               id="email" 
                               name="email" 
                               value="<?= e($email); ?>" 
                               placeholder="name@example.com" 
                               required 
                               autocomplete="email" 
                               autofocus>
                        <?php if (isset($errors['email'])): ?>
                            <div class="invalid-feedback"><?= e($errors['email']); ?></div>
                        <?php else: ?>
                            <div class="invalid-feedback">Please enter your registered email.</div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Password -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label for="password" class="form-label fw-semibold small text-secondary mb-0">Password <span class="text-danger">*</span></label>
                    </div>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted"><i class="bi bi-shield-lock"></i></span>
                        <input type="password" 
                               class="form-control <?= isset($errors['password']) ? 'is-invalid' : ''; ?>" 
                               id="password" 
                               name="password" 
                               placeholder="Enter your password" 
                               required 
                               autocomplete="current-password">
                        <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password">
                            <i class="bi bi-eye"></i>
                        </button>
                        <?php if (isset($errors['password'])): ?>
                            <div class="invalid-feedback"><?= e($errors['password']); ?></div>
                        <?php else: ?>
                            <div class="invalid-feedback">Please enter your password.</div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-primary btn-lg rounded-pill fw-semibold">
                        <i class="bi bi-box-arrow-in-right me-2"></i> Sign In
                    </button>
                </div>
            </form>

            <!-- Quick Demo Credentials Hint Card -->
            <div class="card bg-light border-0 rounded-3 p-3 my-3">
                <div class="d-flex align-items-center mb-1">
                    <i class="bi bi-info-circle-fill text-primary me-2"></i>
                    <span class="fw-semibold small text-dark">Pre-loaded Test Account</span>
                </div>
                <div class="small text-muted">
                    Email: <code>john.doe@example.com</code><br>
                    Password: <code>Password123!</code>
                </div>
            </div>

            <div class="text-center pt-3 border-top">
                <span class="text-muted small">Don't have an account yet?</span>
                <a href="<?= e(base_url('auth/register.php')); ?>" class="small fw-semibold text-primary ms-1 text-decoration-none">Create an Account</a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

