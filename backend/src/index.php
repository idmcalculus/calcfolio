<?php

require __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenvPath = __DIR__ . '/..';
if (file_exists($dotenvPath . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable($dotenvPath);
    $dotenv->load();
}

// --- PHP Configuration for Large Datasets ---
// Increase memory limit and execution time for large data operations
ini_set('memory_limit', '512M'); // Increase from default 128M
ini_set('max_execution_time', '300'); // 5 minutes for large queries
ini_set('max_input_time', '300'); // 5 minutes for large inputs

// --- Session Configuration ---
// Only configure session if it hasn't been started yet and no output has been sent
if (!session_id() && !headers_sent()) {
    // Ensure sessions use secure settings
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_path', '/');
    ini_set('session.cookie_domain', ''); // Let browser determine domain

    $forwardedProto = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '';
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || strtolower($forwardedProto) === 'https';
    $inProd = (($_ENV['APP_ENV'] ?? getenv('APP_ENV')) ?: 'development') === 'production';

    // Cross-site cookies (frontend on a different origin) require SameSite=None; Secure
    $useSecure = ($inProd || $isHttps) ? '1' : '0';
    ini_set('session.cookie_secure', $useSecure);
    ini_set('session.cookie_samesite', $useSecure === '1' ? 'None' : 'Lax');
    ini_set('session.use_strict_mode', '1'); // Prevent session fixation
    ini_set('session.gc_maxlifetime', '3600'); // 1 hour
    ini_set('session.cookie_lifetime', '3600'); // 1 hour

    session_start(); // Start the session
}

// Initialize dependency injection container
$containerBuilder = new \DI\ContainerBuilder();
$containerConfig = require __DIR__ . '/config/container.php';
$container = $containerConfig($containerBuilder);

// Get settings
$settings = $container->get('settings');
$allowedOrigins = $settings['cors']['allowed_origins'];
$isDevelopment = (($_ENV['APP_ENV'] ?? getenv('APP_ENV')) ?: 'development') !== 'production';

// Create Slim app
$app = \Slim\Factory\AppFactory::create();

// Add error handling middleware
$errorMiddleware = $app->addErrorMiddleware($isDevelopment, $isDevelopment, $isDevelopment);

// Add CORS middleware
$app->add($container->get(\App\Presentation\Middleware\CorsMiddleware::class));

// Setup database and create tables
$databaseSetupService = $container->get(\App\Infrastructure\Database\DatabaseSetupService::class);
$databaseSetupService->createTablesIfNotExist();

// Setup Eloquent pagination
\Illuminate\Pagination\Paginator::currentPageResolver(function ($pageName = 'page') {
    return (int) ($_GET[$pageName] ?? 1);
});

// Get controllers
$contactController = $container->get(\App\Application\Controllers\ContactController::class);
$adminAuthController = $container->get(\App\Application\Controllers\AdminAuthController::class);
$adminController = $container->get(\App\Application\Controllers\AdminController::class);
$webhookController = $container->get(\App\Application\Controllers\WebhookController::class);

// Get middleware
$adminAuthMiddleware = $container->get(\App\Presentation\Middleware\AdminAuthMiddleware::class);

// Routes

// Public routes
$app->post('/contact', [$contactController, 'submit']);
$app->get('/message/{messageId}', [$contactController, 'getMessageStatus']);

// Admin authentication routes
$app->map(['GET', 'POST'], '/admin/login', [$adminAuthController, 'login']);
$app->post('/admin/logout', [$adminAuthController, 'logout']);
$app->get('/admin/check', [$adminAuthController, 'checkAuth']);
$app->post('/admin/recover-session', [$adminAuthController, 'recoverSession']);

// Protected admin routes
$app->group('/admin', function ($group) use ($adminController) {
    // More specific routes first
    $group->get('/messages/stats', [$adminController, 'getStatistics']);
    $group->patch('/bulk/messages', [$adminController, 'bulkAction']);
    // More general routes last
    $group->get('/messages/{id}', [$adminController, 'getMessage']);
    $group->get('/messages', [$adminController, 'getMessages']);
})->add($adminAuthMiddleware);

// Resend webhook (public but secured via signature)
$app->post('/resend-webhook', [$webhookController, 'handleResendWebhook']);

// OpenAPI documentation routes
$app->get('/docs.html', function ($request, $response) {
    $docsPath = __DIR__ . '/../public/docs.html';
    if (file_exists($docsPath)) {
        $response->getBody()->write(file_get_contents($docsPath));
        return $response->withHeader('Content-Type', 'text/html');
    }
    throw new \Slim\Exception\HttpNotFoundException($request);
});

$app->get('/openapi.json', function ($request, $response) {
    $openapiPath = __DIR__ . '/../public/openapi.json';
    if (file_exists($openapiPath)) {
        $response->getBody()->write(file_get_contents($openapiPath));
        return $response->withHeader('Content-Type', 'application/json');
    }
    throw new \Slim\Exception\HttpNotFoundException($request);
});

$app->get('/openapi.yaml', function ($request, $response) {
    $openapiPath = __DIR__ . '/../public/openapi.yaml';
    if (file_exists($openapiPath)) {
        $response->getBody()->write(file_get_contents($openapiPath));
        return $response->withHeader('Content-Type', 'application/yaml');
    }
    throw new \Slim\Exception\HttpNotFoundException($request);
});

// Catch-all route for 404
$app->any('[/{path:.*}]', function ($request, $response) {
    throw new \Slim\Exception\HttpNotFoundException($request);
});

// Log server start
\App\Models\EventLog::create([
    'event_type' => 'server_started',
    'payload' => [
        'php_version' => PHP_VERSION,
        'server_time' => date('c'),
        'environment' => ($_ENV['APP_ENV'] ?? getenv('APP_ENV')) ?: 'development'
    ]
]);

$app->run();