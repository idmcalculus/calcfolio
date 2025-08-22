<?php

use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule;

// Default connection settings (can be used for local dev)
$connectionConfig = [
    'driver' => 'pgsql',
    'host' => '127.0.0.1',
    'port' => 5432,
    'database' => 'homestead',
    'username' => 'homestead',
    'password' => 'secret',
    'charset' => 'utf8',
    'prefix' => '',
    'schema' => 'public',
    'sslmode' => 'prefer',
];

// Check if Railway's DATABASE_URL is provided (try both $_ENV and getenv)
$databaseUrl = $_ENV['DATABASE_URL'] ?? getenv('DATABASE_URL');
if ($databaseUrl) {
    $dbUrl = parse_url($databaseUrl);

    $connectionConfig['host'] = $dbUrl['host'];
    $connectionConfig['port'] = $dbUrl['port'];
    $connectionConfig['database'] = ltrim($dbUrl['path'], '/'); // Remove leading slash
    $connectionConfig['username'] = $dbUrl['user'];
    $connectionConfig['password'] = $dbUrl['pass'];
    // Optionally set sslmode based on environment or Railway specifics if needed
    // $connectionConfig['sslmode'] = 'require'; 
} else {
    // Fallback to individual env vars if DATABASE_URL is not set
    $connectionConfig['host'] = $_ENV['PGHOST'] ?? getenv('PGHOST') ?? $connectionConfig['host'];
    $connectionConfig['port'] = $_ENV['PGPORT'] ?? getenv('PGPORT') ?? $connectionConfig['port'];
    $connectionConfig['database'] = $_ENV['PGDATABASE'] ?? getenv('PGDATABASE') ?? $connectionConfig['database'];
    $connectionConfig['username'] = $_ENV['PGUSER'] ?? getenv('PGUSER') ?? $connectionConfig['username'];
    $connectionConfig['password'] = $_ENV['PGPASSWORD'] ?? getenv('PGPASSWORD') ?? $connectionConfig['password'];
    $connectionConfig['sslmode'] = $_ENV['PGSSLMODE'] ?? getenv('PGSSLMODE') ?? $connectionConfig['sslmode'];
}

$capsule->addConnection($connectionConfig);

// Make this Capsule instance available globally via static methods
$capsule->setAsGlobal();

// Setup the Eloquent ORM
$capsule->bootEloquent();

// Return the configuration array if needed by other parts of your application
return $connectionConfig;
