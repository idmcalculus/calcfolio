<?php

require __DIR__ . '/../../vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

// Load database configuration (this returns the full config array)
$dbConfig = require __DIR__ . '/../config/database.php';

// Setup Eloquent Capsule Manager with the complete config
$capsule = new Capsule;
$capsule->addConnection($dbConfig, 'default');

$capsule->setAsGlobal();
$capsule->bootEloquent();

try {
    echo "Starting database schema migration...\n\n";

    // 1. Create 'messages' table
    echo "Creating 'messages' table...\n";
    if (!Capsule::schema()->hasTable('messages')) {
        Capsule::schema()->create('messages', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('subject');
            $table->text('message');
            $table->string('status')->default('pending'); // Default status
            $table->string('message_id')->unique()->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });
        echo "'messages' table created successfully.\n";
    } else {
        echo "'messages' table already exists.\n";
    }

    // 2. Create 'admins' table
    echo "Creating 'admins' table...\n";
    if (!Capsule::schema()->hasTable('admins')) {
        Capsule::schema()->create('admins', function ($table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('password_hash');
            $table->timestamps();
        });
        echo "'admins' table created successfully.\n";
    } else {
        echo "'admins' table already exists.\n";
    }

    // 3. Create 'event_logs' table
    echo "Creating 'event_logs' table...\n";
    if (!Capsule::schema()->hasTable('event_logs')) {
        Capsule::schema()->create('event_logs', function ($table) {
            $table->id();
            $table->string('event_type');
            $table->json('payload')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
        echo "'event_logs' table created successfully.\n";
    } else {
        echo "'event_logs' table already exists.\n";
    }

    echo "\n✅ Database schema migration completed successfully!\n";

} catch (\Exception $e) {
    echo "❌ Error during schema migration: " . $e->getMessage() . "\n";
    exit(1); // Exit with error code
}

exit(0); // Exit successfully
