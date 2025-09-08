<?php

// Setup script to create database tables and indexes
// Run this once after deployment or when database is reset

require __DIR__ . '/src/vendor/autoload.php';

// Load environment variables
$dotenvPath = __DIR__ . '/src';
if (file_exists($dotenvPath . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable($dotenvPath);
    $dotenv->load();
}

// Initialize database connection
$capsule = new \Illuminate\Database\Capsule\Manager;
$settings = require __DIR__ . '/src/config/database.php';
$capsule->addConnection($settings);
$capsule->setAsGlobal();
$capsule->bootEloquent();

// Create tables
$databaseSetupService = new \App\Infrastructure\Database\DatabaseSetupService($capsule);
$databaseSetupService->createTablesIfNotExist();

echo "Database setup completed successfully.\n";