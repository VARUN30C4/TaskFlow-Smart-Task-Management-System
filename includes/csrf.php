<?php
/**
 * TaskFlow – Smart Task Management System
 * Cross-Site Request Forgery (CSRF) Protection Library
 *
 * Implements cryptographically secure token generation using random_bytes,
 * timing-safe token comparison using hash_equals, and token validation.
 */

declare(strict_types=1);

if (!defined('TASKFLOW_APP')) {
    define('TASKFLOW_APP', true);
}

/**
 * Ensure an active PHP session exists with security cookie parameters.
 */
function ensure_session(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        // Enforce secure cookie settings before session starts
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'domain'   => '',
            'secure'   => $isHttps,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        session_start();
    }
}

/**
 * Generate or retrieve the existing CSRF token for the active session.
 *
 * @return string Hexadecimal CSRF token
 */
function generate_csrf_token(): string
{
    ensure_session();

    if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/**
 * Regenerate the CSRF token (useful after login/logout or privilege changes).
 *
 * @return string
 */
function regenerate_csrf_token(): string
{
    ensure_session();
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}

/**
 * Render a hidden HTML input field containing the CSRF token.
 *
 * @return string HTML input tag
 */
function csrf_field(): string
{
    $token = generate_csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * Validate a submitted CSRF token against the session token.
 * Uses hash_equals to protect against timing attacks.
 *
 * @param string|null $token
 * @return bool True if valid, False otherwise
 */
function validate_csrf_token(?string $token = null): bool
{
    ensure_session();

    $submitted = $token ?? ($_POST['csrf_token'] ?? '');
    $sessionToken = $_SESSION['csrf_token'] ?? '';

    if (!is_string($submitted) || $submitted === '' || !is_string($sessionToken) || $sessionToken === '') {
        return false;
    }

    return hash_equals($sessionToken, $submitted);
}

/**
 * Validate CSRF token on state-changing requests, or terminate execution with HTTP 403.
 *
 * @return void
 */
function verify_csrf_or_die(): void
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!validate_csrf_token()) {
            http_response_code(403);
            ?>
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>403 Forbidden – Invalid CSRF Token</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
            </head>
            <body class="bg-light d-flex align-items-center min-vh-100">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-md-6 col-lg-5">
                            <div class="card shadow-lg border-0 rounded-4 p-4 text-center">
                                <div class="text-danger mb-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" fill="currentColor" class="bi bi-shield-slash" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd" d="M1.775 1.542a.5.5 0 0 1 .45-.275H8a7 7 0 0 1 5.727 3.018l.84.84a.5.5 0 0 1-.707.708l-.84-.84A6 6 0 0 0 8 2.267H2.607l-.832-.832a.5.5 0 0 1 0-.707l.001.001.001-.001-.002.002v.001l-.001.001v.001l.001.001zm13.117 12.916a.5.5 0 0 1-.708 0l-1.414-1.414A7 7 0 0 1 8 16c-3.55 0-6.425-2.617-6.937-6.046L.515 9.406a.5.5 0 1 1 .97-.242l.548.548A6 6 0 0 0 8 15c2.46 0 4.607-1.48 5.568-3.625l-.744-.744A5 5 0 0 1 8 14c-2.485 0-4.5-2.015-4.5-4.5 0-.75.184-1.458.51-2.083L1.543 4.95a.5.5 0 0 1 .707-.707l12.642 12.642a.5.5 0 0 1 0 .707z"/>
                                    </svg>
                                </div>
                                <h3 class="fw-bold mb-2">Security Verification Failed</h3>
                                <p class="text-secondary mb-4">
                                    Your request could not be validated due to an invalid or expired CSRF security token. This protects your account against unauthorized actions.
                                </p>
                                <div class="d-grid gap-2">
                                    <a href="javascript:history.back()" class="btn btn-outline-secondary rounded-pill">Go Back</a>
                                    <a href="<?= htmlspecialchars(base_url('tasks/index.php'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-primary rounded-pill">Return to Dashboard</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </body>
            </html>
            <?php
            exit;
        }
    }
}
