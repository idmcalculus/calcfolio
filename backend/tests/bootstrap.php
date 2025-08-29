<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Load test environment variables
$dotenvPath = __DIR__ . '/..';
if (file_exists($dotenvPath . '/.env.testing')) {
    $dotenv = Dotenv\Dotenv::createImmutable($dotenvPath, '.env.testing');
    $dotenv->load();
} elseif (file_exists($dotenvPath . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable($dotenvPath);
    $dotenv->load();
}

// Set up test environment
$_ENV['APP_ENV'] = 'testing';
putenv('APP_ENV=testing');

// Configure error reporting for tests
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

// Mock session for testing
if (!session_id()) {
    session_start();
}

// Clean up any existing test database
$testDbPath = __DIR__ . '/../test.sqlite';
if (file_exists($testDbPath)) {
    unlink($testDbPath);
}