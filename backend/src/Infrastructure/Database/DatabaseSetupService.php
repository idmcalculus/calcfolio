<?php

namespace App\Infrastructure\Database;

use Illuminate\Database\Capsule\Manager as Capsule;
use App\Domain\ValueObjects\MessageStatus;

class DatabaseSetupService
{
    private Capsule $capsule;

    public function __construct(Capsule $capsule)
    {
        $this->capsule = $capsule;
    }

    /**
     * Create all required database tables if they don't exist
     */
    public function createTablesIfNotExist(): void
    {
        $this->createMessagesTable();
        $this->createAdminsTable();
        $this->createEventLogsTable();
    }

    /**
     * Create messages table
     */
    private function createMessagesTable(): void
    {
        if (!$this->capsule->schema()->hasTable('messages')) {
            $this->capsule->schema()->create('messages', function ($table) {
                $table->id();
                $table->string('name');
                $table->string('email');
                $table->string('subject');
                $table->text('message');
                $table->string('status')->default(MessageStatus::STATUS_PENDING);
                $table->string('message_id')->unique()->nullable();
                $table->boolean('is_read')->default(0);
                $table->timestamps();
            });
        }
    }

    /**
     * Create admins table
     */
    private function createAdminsTable(): void
    {
        if (!$this->capsule->schema()->hasTable('admins')) {
            $this->capsule->schema()->create('admins', function ($table) {
                $table->id();
                $table->string('username')->unique();
                $table->string('password_hash');
                $table->timestamps();
            });
        }
    }

    /**
     * Create event_logs table
     */
    private function createEventLogsTable(): void
    {
        if (!$this->capsule->schema()->hasTable('event_logs')) {
            $this->capsule->schema()->create('event_logs', function ($table) {
                $table->id();
                $table->string('event_type');
                $table->json('payload')->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }
    }

    /**
     * Check if all required tables exist
     */
    public function checkTablesExist(): array
    {
        return [
            'messages' => $this->capsule->schema()->hasTable('messages'),
            'admins' => $this->capsule->schema()->hasTable('admins'),
            'event_logs' => $this->capsule->schema()->hasTable('event_logs'),
        ];
    }

    /**
     * Drop all tables (for testing/development)
     */
    public function dropAllTables(): void
    {
        $tables = ['messages', 'admins', 'event_logs'];

        foreach ($tables as $table) {
            if ($this->capsule->schema()->hasTable($table)) {
                $this->capsule->schema()->drop($table);
            }
        }
    }
}