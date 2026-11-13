<?php
/**
 * TaskFlow – Smart Task Management System
 * Core Application Functions and Utilities
 *
 * Implements sanitization, XSS escaping, URL resolution, flash messages,
 * date formatting, and status badge rendering.
 */

declare(strict_types=1);

if (!defined('TASKFLOW_APP')) {
    define('TASKFLOW_APP', true);
}

/**
 * Escape user-provided strings for safe output in HTML contexts (XSS Protection).
 *
 * @param mixed $value
 * @return string
 */
function e(mixed $value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
}

/**
 * Sanitize user input by trimming whitespace.
 *
 * @param mixed $data
 * @return mixed
 */
function sanitize_input(mixed $data): mixed
{
    if (is_array($data)) {
        return array_map('sanitize_input', $data);
    }
    if (is_string($data)) {
        return trim($data);
    }
    return $data;
}

/**
 * Dynamically resolves the base URL for the application.
 * Works seamlessly across XAMPP subdirectories, Apache VirtualHosts,
 * and the PHP built-in development server.
 *
 * @param string $path
 * @return string
 */
function base_url(string $path = ''): string
{
    static $computedBase = null;

    if ($computedBase === null) {
        $envUrl = getenv('APP_URL') ?: ($_ENV['APP_URL'] ?? '');
        if (!empty($envUrl)) {
            $computedBase = rtrim($envUrl, '/');
        } else {
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

            $docRoot = isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : '';
            $appRoot = realpath(dirname(__DIR__));

            if ($docRoot && $appRoot && str_starts_with($appRoot, $docRoot)) {
                $subDir = substr($appRoot, strlen($docRoot));
                $subDir = str_replace('\\', '/', $subDir);
                $computedBase = $protocol . $host . rtrim($subDir, '/');
            } else {
                // Fallback deduction based on SCRIPT_NAME
                $script = $_SERVER['SCRIPT_NAME'] ?? '';
                $dir = dirname($script);
                $dirName = basename($dir);
                if (in_array($dirName, ['auth', 'tasks', 'includes', 'config'], true)) {
                    $dir = dirname($dir);
                }
                $dir = str_replace('\\', '/', $dir);
                $computedBase = $protocol . $host . rtrim($dir, '/');
            }
        }
    }

    $cleanPath = ltrim($path, '/');
    return $cleanPath === '' ? $computedBase : "{$computedBase}/{$cleanPath}";
}

/**
 * Redirect to a given URL or relative path and terminate script execution.
 *
 * @param string $url
 * @param int $statusCode
 * @return never
 */
function redirect(string $url, int $statusCode = 303): never
{
    // If URL does not start with http:// or https://, resolve using base_url
    if (!str_starts_with($url, 'http://') && !str_starts_with($url, 'https://')) {
        $url = base_url($url);
    }

    header("Location: {$url}", true, $statusCode);
    exit;
}

/**
 * Set a one-time flash message in the active session.
 *
 * @param string $type Bootstrap alert type: 'success', 'danger', 'warning', 'info'
 * @param string $message Flash message text
 */
function set_flash_message(string $type, string $message): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['flash'] = [
        'type'    => in_array($type, ['success', 'danger', 'warning', 'info'], true) ? $type : 'info',
        'message' => $message,
    ];
}

/**
 * Retrieve and clear the flash message from session.
 *
 * @return array{type: string, message: string}|null
 */
function get_flash_message(): ?array
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }

    return null;
}

/**
 * Render Bootstrap 5 visually distinguishable status badges.
 *
 * @param string $status
 * @return string
 */
function get_status_badge(string $status): string
{
    return match ($status) {
        'Pending' => '<span class="badge badge-status badge-pending text-dark"><i class="bi bi-hourglass-split me-1"></i>Pending</span>',
        'In Progress' => '<span class="badge badge-status badge-in-progress text-dark"><i class="bi bi-arrow-repeat me-1"></i>In Progress</span>',
        'Completed' => '<span class="badge badge-status badge-completed"><i class="bi bi-check-circle-fill me-1"></i>Completed</span>',
        default => '<span class="badge bg-secondary"><i class="bi bi-question-circle me-1"></i>' . e($status) . '</span>',
    };
}

/**
 * Format a database TIMESTAMP / DATETIME into a human-readable string.
 *
 * @param string|null $datetime
 * @return string
 */
function format_datetime(?string $datetime): string
{
    if (!$datetime) {
        return '—';
    }

    try {
        $dt = new DateTime($datetime);
        return $dt->format('M d, Y h:i A');
    } catch (Exception) {
        return e($datetime);
    }
}
