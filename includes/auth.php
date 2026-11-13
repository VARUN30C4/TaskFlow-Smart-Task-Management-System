<?php
/**
 * TaskFlow – Smart Task Management System
 * Authentication and Session Management Service
 *
 * Enforces session security, login validation, session regeneration,
 * and route protection guards.
 */

declare(strict_types=1);

if (!defined('TASKFLOW_APP')) {
    define('TASKFLOW_APP', true);
}

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/csrf.php';

// Initialize session with secure configuration
ensure_session();

/**
 * Determine whether a user is currently authenticated.
 *
 * @return bool
 */
function is_logged_in(): bool
{
    return isset($_SESSION['user_id']) && is_numeric($_SESSION['user_id']) && (int)$_SESSION['user_id'] > 0;
}

/**
 * Get the currently authenticated user's ID.
 *
 * @return int|null
 */
function current_user_id(): ?int
{
    return is_logged_in() ? (int)$_SESSION['user_id'] : null;
}

/**
 * Get the currently authenticated user array.
 *
 * @return array{id: int, full_name: string, email: string}|null
 */
function current_user(): ?array
{
    if (!is_logged_in() || !isset($_SESSION['user'])) {
        return null;
    }

    return $_SESSION['user'];
}

/**
 * Enforce authentication for protected pages.
 * Redirects unauthorized visitors to the login page.
 *
 * @return void
 */
function require_auth(): void
{
    if (!is_logged_in()) {
        set_flash_message('warning', 'Please sign in to access your TaskFlow dashboard.');
        redirect('auth/login.php');
    }
}

/**
 * Ensure the visitor is a guest (not logged in).
 * If already logged in, redirects directly to the task dashboard.
 *
 * @return void
 */
function require_guest(): void
{
    if (is_logged_in()) {
        redirect('tasks/index.php');
    }
}

/**
 * Log in a user securely by regenerating session ID and storing session state.
 *
 * @param array{id: int, full_name: string, email: string} $userData
 * @return void
 */
function login_user(array $userData): void
{
    ensure_session();

    // Prevent session fixation by generating a new session ID
    session_regenerate_id(true);

    $_SESSION['user_id'] = (int)$userData['id'];
    $_SESSION['user'] = [
        'id'        => (int)$userData['id'],
        'full_name' => (string)$userData['full_name'],
        'email'     => (string)$userData['email'],
    ];

    // Refresh CSRF token for the new authenticated session
    regenerate_csrf_token();
}

/**
 * Log out the current user, destroying all session data and cookies.
 *
 * @return void
 */
function logout_user(): void
{
    ensure_session();

    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();
}

