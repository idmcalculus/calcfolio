<?php
// Diagnostic endpoint to check server configuration
// Access at: https://calcfolio-api-dev.up.railway.app/diagnostic

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Disable error output
ini_set('display_errors', '0');
error_reporting(E_ALL);

$diagnostics = [
    'timestamp' => date('c'),
    'php_version' => PHP_VERSION,
    'server' => [
        'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'] ?? 'not set',
        'HTTP_HOST' => $_SERVER['HTTP_HOST'] ?? 'not set',
        'SERVER_NAME' => $_SERVER['SERVER_NAME'] ?? 'not set',
        'HTTP_X_FORWARDED_PROTO' => $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? 'not set',
        'HTTP_X_FORWARDED_HOST' => $_SERVER['HTTP_X_FORWARDED_HOST'] ?? 'not set',
        'HTTPS' => $_SERVER['HTTPS'] ?? 'not set',
        'HTTP_ORIGIN' => $_SERVER['HTTP_ORIGIN'] ?? 'not set'
    ],
    'environment' => [
        'APP_ENV' => $_ENV['APP_ENV'] ?? getenv('APP_ENV') ?: 'not set',
        'CORS_ALLOWED_ORIGINS' => $_ENV['CORS_ALLOWED_ORIGINS'] ?? getenv('CORS_ALLOWED_ORIGINS') ?: 'not set'
    ],
    'session' => [
        'status' => session_status(),
        'status_text' => session_status() === PHP_SESSION_ACTIVE ? 'active' : 
                         (session_status() === PHP_SESSION_DISABLED ? 'disabled' : 'none'),
        'id' => session_id() ?: 'no session',
        'cookie_params' => session_get_cookie_params()
    ],
    'ini_settings' => [
        'display_errors' => ini_get('display_errors'),
        'error_reporting' => error_reporting(),
        'session.cookie_secure' => ini_get('session.cookie_secure'),
        'session.cookie_samesite' => ini_get('session.cookie_samesite'),
        'session.cookie_httponly' => ini_get('session.cookie_httponly'),
        'session.cookie_domain' => ini_get('session.cookie_domain'),
        'session.cookie_path' => ini_get('session.cookie_path')
    ],
    'headers_sent' => headers_sent(),
    'output_buffering' => ob_get_level()
];

// Try to start session if not active
if (session_status() !== PHP_SESSION_ACTIVE) {
    if (!headers_sent()) {
        session_start();
        $diagnostics['session']['started_by_diagnostic'] = true;
        $diagnostics['session']['new_status'] = session_status() === PHP_SESSION_ACTIVE ? 'active' : 'failed';
    } else {
        $diagnostics['session']['cannot_start'] = 'headers already sent';
    }
}

echo json_encode($diagnostics, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);