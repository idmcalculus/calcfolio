<?php

require __DIR__ . '/../../vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

// Load database configuration
$dbConfig = require __DIR__ . '/../config/database.php';

// Setup Eloquent Capsule Manager
$capsule = new Capsule;
$capsule->addConnection([
    'driver'   => $dbConfig['driver'],
    'database' => $dbConfig['database'],
    'prefix'   => $dbConfig['prefix'] ?? '',
], 'default');

$capsule->setAsGlobal();
$capsule->bootEloquent();

// Get the PDO connection
$pdo = Capsule::connection()->getPdo();

try {
    // 1. Add 'is_read' column to 'messages' table
    echo "Attempting to add 'is_read' column to 'messages' table...\n";
    // Check if column exists first to make script idempotent
    $stmt = $pdo->query("PRAGMA table_info(messages);");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN, 1); // Fetch column names

    if (!in_array('is_read', $columns)) {
        $pdo->exec("ALTER TABLE messages ADD COLUMN is_read INTEGER DEFAULT 0;");
        echo "'is_read' column added successfully.\n";
    } else {
        echo "'is_read' column already exists.\n";
    }

    // 2. Create 'admins' table if it doesn't exist
    echo "Attempting to create 'admins' table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS admins (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE NOT NULL,
        password_hash TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );");
    echo "'admins' table checked/created successfully.\n";

    echo "\nSchema migration completed.\n";

} catch (\PDOException $e) {
    echo "Error during schema migration: " . $e->getMessage() . "\n";
    exit(1); // Exit with error code
}

exit(0); // Exit successfully
