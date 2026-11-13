<?php
/**
 * TaskFlow – Smart Task Management System
 * User Registration Page
 *
 * Implements input validation, email duplicate prevention,
 * secure password hashing (BCRYPT), and automatic session onboarding.
 */

declare(strict_types=1);

define('TASKFLOW_APP', true);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/auth.php';

// Prevent already logged-in users from viewing the registration page
require_guest();

$errors = [];
$formData = [
    'full_name' => '',
    'email'     => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Validate CSRF Protection
    verify_csrf_or_die();

    // 2. Collect and sanitize input
    $fullName        = sanitize_input($_POST['full_name'] ?? '');
    $email           = sanitize_input($_POST['email'] ?? '');
    $password        = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    $formData['full_name'] = $fullName;
    $formData['email']     = $email;

    // 3. Backend Validation
    if ($fullName === '') {
        $errors['full_name'] = 'Full Name is required.';
    } elseif (mb_strlen($fullName) < 2 || mb_strlen($fullName) > 100) {
        $errors['full_name'] = 'Full Name must be between 2 and 100 characters.';
    }

    if ($email === '') {
        $errors['email'] = 'Email address is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    } elseif (mb_strlen($email) > 150) {
        $errors['email'] = 'Email address must not exceed 150 characters.';
    }

    if ($password === '') {
        $errors['password'] = 'Password is required.';
    } elseif (strlen($password) < 8) {
        $errors['password'] = 'Password must be at least 8 characters long.';
    }

    if ($confirmPassword === '') {
        $errors['confirm_password'] = 'Please confirm your password.';
    } elseif ($password !== $confirmPassword) {
        $errors['confirm_password'] = 'Passwords do not match.';
    }

    // 4. Check for duplicate email in database
    if (empty($errors)) {
        $pdo = getDBConnection();
        $checkStmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
        $checkStmt->execute([':email' => $email]);
        if ($checkStmt->fetch()) {
            $errors['email'] = 'This email address is already registered. Please log in instead.';
        }
    }

    // 5. Create user record if validation succeeds
    if (empty($errors)) {
        $pdo = getDBConnection();
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $insertStmt = $pdo->prepare('
            INSERT INTO users (full_name, email, password, created_at) 
            VALUES (:full_name, :email, :password, NOW())
        ');

        $success = $insertStmt->execute([
            ':full_name' => $fullName,
            ':email'     => $email,
            ':password'  => $passwordHash,
        ]);

        if ($success) {
            $newUserId = (int)$pdo->lastInsertId();

            // Automatically log in the newly registered user
            login_user([
                'id'        => $newUserId,
                'full_name' => $fullName,
                'email'     => $email,
            ]);

            set_flash_message('success', 'Registration successful! Welcome to your TaskFlow dashboard.');
            redirect('tasks/index.php');
        } else {
            $errors['general'] = 'Failed to create your account. Please try again later.';
        }
    }
}

$pageTitle = 'Create an Account – TaskFlow';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-7 col-lg-5">
        <div class="card card-custom p-4 p-md-5 my-3">
            <div class="text-center mb-4">
                <div class="d-inline-flex p-3 bg-primary-subtle text-primary rounded-circle mb-3">
                    <i class="bi bi-person-plus-fill fs-2"></i>
                </div>
                <h3 class="fw-bold text-dark mb-1">Create Account</h3>
                <p class="text-muted small">Join TaskFlow to organize your tasks efficiently</p>
            </div>

            <?php if (!empty($errors['general'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle-fill me-2"></i><?= e($errors['general']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form action="<?= e(base_url('auth/register.php')); ?>" method="POST" id="registerForm" novalidate class="needs-validation">
                <?= csrf_field(); ?>

                <!-- Full Name -->
                <div class="mb-3">
                    <label for="full_name" class="form-label fw-semibold small text-secondary">Full Name <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted"><i class="bi bi-person"></i></span>
                        <input type="text" 
                               class="form-control <?= isset($errors['full_name']) ? 'is-invalid' : ''; ?>" 
                               id="full_name" 
                               name="full_name" 
                               value="<?= e($formData['full_name']); ?>" 
                               placeholder="e.g. Varun Kumar" 
                               required 
                               autocomplete="name">
                        <?php if (isset($errors['full_name'])): ?>
                            <div class="invalid-feedback"><?= e($errors['full_name']); ?></div>
                        <?php else: ?>
                            <div class="invalid-feedback">Please enter your full name (2–100 characters).</div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Email Address -->
                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold small text-secondary">Email Address <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted"><i class="bi bi-envelope"></i></span>
                        <input type="email" 
                               class="form-control <?= isset($errors['email']) ? 'is-invalid' : ''; ?>" 
                               id="email" 
                               name="email" 
                               value="<?= e($formData['email']); ?>" 
                               placeholder="name@example.com" 
                               required 
                               autocomplete="email">
                        <?php if (isset($errors['email'])): ?>
                            <div class="invalid-feedback"><?= e($errors['email']); ?></div>
                        <?php else: ?>
                            <div class="invalid-feedback">Please enter a valid email address.</div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Password -->
                <div class="mb-3">
                    <label for="password" class="form-label fw-semibold small text-secondary">Password <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted"><i class="bi bi-shield-lock"></i></span>
                        <input type="password" 
                               class="form-control <?= isset($errors['password']) ? 'is-invalid' : ''; ?>" 
                               id="password" 
                               name="password" 
                               placeholder="Minimum 8 characters" 
                               minlength="8" 
                               required 
                               autocomplete="new-password">
                        <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password">
                            <i class="bi bi-eye"></i>
                        </button>
                        <?php if (isset($errors['password'])): ?>
                            <div class="invalid-feedback"><?= e($errors['password']); ?></div>
                        <?php else: ?>
                            <div class="invalid-feedback">Password must be at least 8 characters.</div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Confirm Password -->
                <div class="mb-4">
                    <label for="confirm_password" class="form-label fw-semibold small text-secondary">Confirm Password <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted"><i class="bi bi-check2-circle"></i></span>
                        <input type="password" 
                               class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : ''; ?>" 
                               id="confirm_password" 
                               name="confirm_password" 
                               placeholder="Re-type password" 
                               required 
                               autocomplete="new-password">
                        <button class="btn btn-outline-secondary toggle-password" type="button" data-target="confirm_password">
                            <i class="bi bi-eye"></i>
                        </button>
                        <?php if (isset($errors['confirm_password'])): ?>
                            <div class="invalid-feedback"><?= e($errors['confirm_password']); ?></div>
                        <?php else: ?>
                            <div class="invalid-feedback">Passwords must match.</div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-primary btn-lg rounded-pill fw-semibold">
                        <i class="bi bi-arrow-right-circle me-2"></i> Register Account
                    </button>
                </div>
            </form>

            <div class="text-center pt-3 border-top">
                <span class="text-muted small">Already have an account?</span>
                <a href="<?= e(base_url('auth/login.php')); ?>" class="small fw-semibold text-primary ms-1 text-decoration-none">Sign In here</a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
