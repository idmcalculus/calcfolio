<?php

namespace App\Tests\Unit\Infrastructure\Database;

use App\Infrastructure\Database\DatabaseSetupService;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use PHPUnit\Framework\TestCase;
use Mockery as m;

class DatabaseSetupServiceTest extends TestCase
{
    private DatabaseSetupService $service;
    private Capsule $capsule;
    private $schema;

    protected function setUp(): void
    {
        $this->capsule = m::mock(Capsule::class);
        $this->schema = m::mock();

        $this->capsule->shouldReceive('schema')
            ->andReturn($this->schema);

        $this->service = new DatabaseSetupService($this->capsule);
    }

    protected function tearDown(): void
    {
        m::close();
    }

    public function testConstructor(): void
    {
        $this->assertInstanceOf(DatabaseSetupService::class, $this->service);
    }

    public function testCreateTablesIfNotExistCreatesAllTables(): void
    {
        // Mock that tables don't exist initially
        $this->schema->shouldReceive('hasTable')
            ->with('messages')
            ->andReturn(false);

        $this->schema->shouldReceive('hasTable')
            ->with('admins')
            ->andReturn(false);

        $this->schema->shouldReceive('hasTable')
            ->with('event_logs')
            ->andReturn(false);

        // Mock table creation
        $this->schema->shouldReceive('create')
            ->with('messages', m::on(function ($callback) {
                $table = m::mock();
                $table->shouldReceive('id')->once();
                $table->shouldReceive('string')->with('name')->once();
                $table->shouldReceive('string')->with('email')->once();
                $table->shouldReceive('string')->with('subject')->once();
                $table->shouldReceive('text')->with('message')->once();
                $table->shouldReceive('string')->with('status')->once()->andReturn($table);
                $table->shouldReceive('default')->once()->andReturn($table);
                $table->shouldReceive('string')->with('message_id')->once()->andReturn($table);
                $table->shouldReceive('unique')->once()->andReturn($table);
                $table->shouldReceive('nullable')->once()->andReturn($table);
                $table->shouldReceive('boolean')->with('is_read')->once()->andReturn($table);
                $table->shouldReceive('default')->with(0)->once()->andReturn($table);
                $table->shouldReceive('timestamps')->once();

                $callback($table);
                return true;
            }))
            ->once();

        $this->schema->shouldReceive('create')
            ->with('admins', m::on(function ($callback) {
                $table = m::mock();
                $table->shouldReceive('id')->once();
                $table->shouldReceive('string')->with('username')->once()->andReturn($table);
                $table->shouldReceive('unique')->once()->andReturn($table);
                $table->shouldReceive('string')->with('password_hash')->once();
                $table->shouldReceive('timestamps')->once();

                $callback($table);
                return true;
            }))
            ->once();

        $this->schema->shouldReceive('create')
            ->with('event_logs', m::on(function ($callback) {
                $table = m::mock();
                $table->shouldReceive('id')->once();
                $table->shouldReceive('string')->with('event_type')->once();
                $table->shouldReceive('json')->with('payload')->once()->andReturn($table);
                $table->shouldReceive('nullable')->once()->andReturn($table);
                $table->shouldReceive('timestamp')->with('created_at')->once()->andReturn($table);
                $table->shouldReceive('useCurrent')->once()->andReturn($table);

                $callback($table);
                return true;
            }))
            ->once();

        $this->service->createTablesIfNotExist();
    }

    public function testCreateTablesIfNotExistSkipsExistingTables(): void
    {
        // Mock that all tables already exist
        $this->schema->shouldReceive('hasTable')
            ->with('messages')
            ->andReturn(true);

        $this->schema->shouldReceive('hasTable')
            ->with('admins')
            ->andReturn(true);

        $this->schema->shouldReceive('hasTable')
            ->with('event_logs')
            ->andReturn(true);

        // Should not call create for any table
        $this->schema->shouldNotReceive('create');

        $this->service->createTablesIfNotExist();
    }

    public function testCheckTablesExistReturnsCorrectStatus(): void
    {
        $this->schema->shouldReceive('hasTable')
            ->with('messages')
            ->andReturn(true);

        $this->schema->shouldReceive('hasTable')
            ->with('admins')
            ->andReturn(false);

        $this->schema->shouldReceive('hasTable')
            ->with('event_logs')
            ->andReturn(true);

        $result = $this->service->checkTablesExist();

        $expected = [
            'messages' => true,
            'admins' => false,
            'event_logs' => true,
        ];

        $this->assertEquals($expected, $result);
    }

    public function testDropAllTablesDropsExistingTables(): void
    {
        $this->schema->shouldReceive('hasTable')
            ->with('messages')
            ->andReturn(true);

        $this->schema->shouldReceive('hasTable')
            ->with('admins')
            ->andReturn(false);

        $this->schema->shouldReceive('hasTable')
            ->with('event_logs')
            ->andReturn(true);

        $this->schema->shouldReceive('drop')
            ->with('messages')
            ->once();

        $this->schema->shouldReceive('drop')
            ->with('event_logs')
            ->once();

        // Should not try to drop admins table since it doesn't exist
        $this->schema->shouldNotReceive('drop')
            ->with('admins');

        $this->service->dropAllTables();
    }

    public function testDropAllTablesWhenNoTablesExist(): void
    {
        $this->schema->shouldReceive('hasTable')
            ->with('messages')
            ->andReturn(false);

        $this->schema->shouldReceive('hasTable')
            ->with('admins')
            ->andReturn(false);

        $this->schema->shouldReceive('hasTable')
            ->with('event_logs')
            ->andReturn(false);

        // Should not call drop for any table
        $this->schema->shouldNotReceive('drop');

        $this->service->dropAllTables();
    }

    public function testCreateMessagesTableStructure(): void
    {
        $this->schema->shouldReceive('hasTable')
            ->with('messages')
            ->andReturn(false);

        $this->schema->shouldReceive('create')
            ->with('messages', m::type('callable'))
            ->once();

        // Test private method indirectly through public method
        $this->service->createTablesIfNotExist();
    }

    public function testCreateAdminsTableStructure(): void
    {
        $this->schema->shouldReceive('hasTable')
            ->with('messages')
            ->andReturn(true); // Skip messages table

        $this->schema->shouldReceive('hasTable')
            ->with('admins')
            ->andReturn(false);

        $this->schema->shouldReceive('hasTable')
            ->with('event_logs')
            ->andReturn(true); // Skip event_logs table

        $this->schema->shouldReceive('create')
            ->with('admins', m::type('callable'))
            ->once();

        $this->service->createTablesIfNotExist();
    }

    public function testCreateEventLogsTableStructure(): void
    {
        $this->schema->shouldReceive('hasTable')
            ->with('messages')
            ->andReturn(true); // Skip messages table

        $this->schema->shouldReceive('hasTable')
            ->with('admins')
            ->andReturn(true); // Skip admins table

        $this->schema->shouldReceive('hasTable')
            ->with('event_logs')
            ->andReturn(false);

        $this->schema->shouldReceive('create')
            ->with('event_logs', m::type('callable'))
            ->once();

        $this->service->createTablesIfNotExist();
    }

    public function testCheckTablesExistWithAllTablesMissing(): void
    {
        $this->schema->shouldReceive('hasTable')
            ->with('messages')
            ->andReturn(false);

        $this->schema->shouldReceive('hasTable')
            ->with('admins')
            ->andReturn(false);

        $this->schema->shouldReceive('hasTable')
            ->with('event_logs')
            ->andReturn(false);

        $result = $this->service->checkTablesExist();

        $expected = [
            'messages' => false,
            'admins' => false,
            'event_logs' => false,
        ];

        $this->assertEquals($expected, $result);
    }

    public function testCheckTablesExistWithAllTablesPresent(): void
    {
        $this->schema->shouldReceive('hasTable')
            ->with('messages')
            ->andReturn(true);

        $this->schema->shouldReceive('hasTable')
            ->with('admins')
            ->andReturn(true);

        $this->schema->shouldReceive('hasTable')
            ->with('event_logs')
            ->andReturn(true);

        $result = $this->service->checkTablesExist();

        $expected = [
            'messages' => true,
            'admins' => true,
            'event_logs' => true,
        ];

        $this->assertEquals($expected, $result);
    }

    public function testServiceHandlesSchemaExceptionsGracefully(): void
    {
        $this->schema->shouldReceive('hasTable')
            ->with('messages')
            ->andThrow(new \Exception('Database connection failed'));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Database connection failed');

        $this->service->createTablesIfNotExist();
    }

    public function testServiceHandlesCheckTablesExistExceptions(): void
    {
        $this->schema->shouldReceive('hasTable')
            ->with('messages')
            ->andThrow(new \Exception('Database connection failed'));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Database connection failed');

        $this->service->checkTablesExist();
    }

    public function testServiceHandlesDropAllTablesExceptions(): void
    {
        $this->schema->shouldReceive('hasTable')
            ->with('messages')
            ->andThrow(new \Exception('Database connection failed'));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Database connection failed');

        $this->service->dropAllTables();
    }
}