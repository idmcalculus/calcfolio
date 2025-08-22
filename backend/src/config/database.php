<?php

use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule;

// Check if Railway's DATABASE_URL is provided (try both $_ENV and getenv)
$databaseUrl = $_ENV['DATABASE_URL'] ?? getenv('DATABASE_URL');
if ($databaseUrl) {
    // Use PostgreSQL from DATABASE_URL (Railway production)
    $dbUrl = parse_url($databaseUrl);

    $connectionConfig = [
        'driver' => 'pgsql',
        'host' => $dbUrl['host'],
        'port' => $dbUrl['port'],
        'database' => ltrim($dbUrl['path'], '/'), // Remove leading slash
        'username' => $dbUrl['user'],
        'password' => $dbUrl['pass'],
        'charset' => 'utf8',
        'prefix' => '',
        'schema' => 'public',
        'sslmode' => 'prefer',
    ];
} else {
    // Use SQLite for local development
    $dbPath = $_ENV['DB_PATH'] ?? getenv('DB_PATH') ?? 'contact.db';
    $connectionConfig = [
        'driver' => 'sqlite',
        'database' => __DIR__ . '/../../' . $dbPath,
        'prefix' => '',
    ];
}

$capsule->addConnection($connectionConfig);

// Make this Capsule instance available globally via static methods
$capsule->setAsGlobal();

// Setup the Eloquent ORM
$capsule->bootEloquent();

// Return the configuration array if needed by other parts of your application
return $connectionConfig;
