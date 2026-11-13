<?php
/**
 * TaskFlow – Smart Task Management System
 * Database Configuration and PDO Connection Provider
 *
 * Designed for RTIH Amaravati Foundation Technical Assignment.
 * Uses PHP PDO with strict error reporting and prepared statements.
 */

declare(strict_types=1);

// Prevent direct script execution if accessed outside root application context
if (!defined('TASKFLOW_APP')) {
    define('TASKFLOW_APP', true);
}

/**
 * Lightweight helper to load .env variables if .env file exists.
 * Does not overwrite existing environment variables.
 */
function loadEnvFile(string $filePath): void
{
    if (!file_exists($filePath) || !is_readable($filePath)) {
        return;
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $key = trim($parts[0]);
            $val = trim($parts[1]);
            // Strip enclosing quotes if present
            if (
                (str_starts_with($val, '"') && str_ends_with($val, '"')) ||
                (str_starts_with($val, "'") && str_ends_with($val, "'"))
            ) {
                $val = substr($val, 1, -1);
            }

            if (!isset($_ENV[$key]) && getenv($key) === false) {
                putenv("{$key}={$val}");
                $_ENV[$key] = $val;
            }
        }
    }
}

// Load environment variables if .env exists
$envPath = dirname(__DIR__) . '/.env';
if (file_exists($envPath)) {
    loadEnvFile($envPath);
}

/**
 * Get the PDO Database connection.
 * Uses a static instance for connection pooling within the same request lifecycle.
 *
 * @throws PDOException
 * @return PDO
 */
function getDBConnection(): PDO
{
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    // Default configuration (Compatible with XAMPP defaults)
    $host = getenv('DB_HOST') ?: ($_ENV['DB_HOST'] ?? '127.0.0.1');
    $port = getenv('DB_PORT') ?: ($_ENV['DB_PORT'] ?? '3306');
    $db   = getenv('DB_NAME') ?: ($_ENV['DB_NAME'] ?? 'taskflow_db');
    $user = getenv('DB_USER') ?: ($_ENV['DB_USER'] ?? 'root');
    $pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : ($_ENV['DB_PASS'] ?? '');
    $charset = getenv('DB_CHARSET') ?: ($_ENV['DB_CHARSET'] ?? 'utf8mb4');

    $dsn = "mysql:host={$host};port={$port};dbname={$db};charset={$charset}";

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$charset} COLLATE {$charset}_unicode_ci",
    ];

    try {
        $pdo = new PDO($dsn, (string)$user, (string)$pass, $options);
        return $pdo;
    } catch (PDOException $e) {
        // Log the detailed database connection error for administrators/developers
        error_log("[TaskFlow DB Connection Error] " . $e->getMessage());

        $isDebug = (getenv('APP_DEBUG') === 'true' || ($_ENV['APP_DEBUG'] ?? '') === 'true');

        // Present a user-friendly error page without exposing raw credentials
        http_response_code(500);
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Database Connection Error – TaskFlow</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        </head>
        <body class="bg-light d-flex align-items-center min-vh-100">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-7 col-lg-6">
                        <div class="card shadow-lg border-0 rounded-4 p-4 text-center">
                            <div class="text-danger mb-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" class="bi bi-exclamation-triangle" viewBox="0 0 16 16">
                                    <path d="M7.938 2.016A.13.13 0 0 1 8.002 2a.13.13 0 0 1 .063.016.15.15 0 0 1 .054.057l6.857 11.667c.036.06.035.124.002.183a.2.2 0 0 1-.054.06.1.1 0 0 1-.066.017H1.146a.1.1 0 0 1-.066-.017.2.2 0 0 1-.054-.06.18.18 0 0 1 .002-.183L7.884 2.073a.15.15 0 0 1 .054-.057m1.044-.45a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767z"/>
                                    <path d="M7.002 12a1 1 0 1 1 2 0 1 1 0 0 1-2 0M7.1 5.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0z"/>
                                </svg>
                            </div>
                            <h3 class="fw-bold mb-2">Database Connection Required</h3>
                            <p class="text-secondary mb-4">
                                TaskFlow could not connect to the MySQL database. Please make sure MySQL is running in your XAMPP Control Panel and the <code>taskflow_db</code> database has been imported.
                            </p>
                            <div class="alert alert-info text-start small">
                                <strong>Setup Checklist:</strong>
                                <ul class="mb-0 mt-2 ps-3">
                                    <li>Open <strong>XAMPP Control Panel</strong> and click <strong>Start</strong> on MySQL.</li>
                                    <li>Open <strong>phpMyAdmin</strong> (<a href="http://localhost/phpmyadmin" target="_blank" class="alert-link">http://localhost/phpmyadmin</a>).</li>
                                    <li>Import the <code>database.sql</code> file provided in the project root.</li>
                                    <li>Verify credentials in <code>config/database.php</code> (default: user <code>root</code>, blank password).</li>
                                </ul>
                            </div>
                            <?php if ($isDebug): ?>
                                <div class="alert alert-secondary text-start small mt-2">
                                    <strong>Debug Notice:</strong> <?= htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                            <?php endif; ?>
                            <div>
                                <a href="javascript:location.reload()" class="btn btn-primary px-4 rounded-pill">Retry Connection</a>
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
