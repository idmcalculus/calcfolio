<?php

require __DIR__ . '/../../vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;

// Check for command-line arguments
if ($argc < 3) {
    echo "Usage: php create_admin.php <username> <password>\n";
    exit(1);
}

$username = $argv[1];
$password = $argv[2];

// Load environment variables (optional for production)
$dotenvPath = __DIR__ . '/../../';
if (file_exists($dotenvPath . '.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable($dotenvPath);
    $dotenv->load();
}

// Load database configuration (this returns the full config array)
$dbConfig = require __DIR__ . '/../config/database.php';

// Setup Eloquent Capsule Manager with the complete config
$capsule = new Capsule;
$capsule->addConnection($dbConfig, 'default');

$capsule->setAsGlobal();
$capsule->bootEloquent();

// Get the PDO connection for direct query or use Eloquent model if preferred
$pdo = Capsule::connection()->getPdo();

try {
    // Check if username already exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetchColumn() > 0) {
        echo "Error: Username '{$username}' already exists.\n";
        exit(1);
    }

    // Hash the password
    $passwordHash = password_hash($password, PASSWORD_DEFAULT); // PASSWORD_DEFAULT is recommended

    if ($passwordHash === false) {
        echo "Error: Failed to hash password.\n";
        exit(1);
    }

    // Insert the new admin user
    $insertStmt = $pdo->prepare("INSERT INTO admins (username, password_hash) VALUES (?, ?)");
    $success = $insertStmt->execute([$username, $passwordHash]);

    if ($success) {
        echo "Admin user '{$username}' created successfully.\n";
        exit(0); // Exit successfully
    } else {
        echo "Error: Failed to insert admin user into database.\n";
        exit(1);
    }

} catch (\PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
    exit(1); // Exit with error code
} catch (\Exception $e) {
    echo "An error occurred: " . $e->getMessage() . "\n";
    exit(1);
}
