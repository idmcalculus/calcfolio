<?php

namespace App\Tests\Unit\Infrastructure\Database;

use App\Domain\Entities\Message;
use App\Domain\ValueObjects\EmailAddress;
use App\Domain\ValueObjects\MessageStatus;
use App\Infrastructure\Database\EloquentMessageRepository;
use App\Models\Message as MessageModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use PHPUnit\Framework\TestCase;
use Mockery as m;
use DateTimeImmutable;

class EloquentMessageRepositoryTest extends TestCase
{
    private EloquentMessageRepository $repository;
    private MessageModel $messageModel;

    protected function setUp(): void
    {
        $this->repository = new EloquentMessageRepository();
        $this->messageModel = m::mock(MessageModel::class);
    }

    protected function tearDown(): void
    {
        m::close();
    }

    public function testFindByIdReturnsMessageWhenFound(): void
    {
        $modelData = [
            'id' => 1,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'subject' => 'Test Subject',
            'message' => 'Test message',
            'message_id' => 'msg_123',
            'status' => 'pending',
            'is_read' => false,
            'created_at' => '2023-01-01 10:00:00',
            'updated_at' => '2023-01-01 11:00:00',
        ];

        $model = m::mock(MessageModel::class);
        $model->shouldReceive('getAttributes')->andReturn($modelData);
        $model->id = 1;
        $model->name = 'John Doe';
        $model->email = 'john@example.com';
        $model->subject = 'Test Subject';
        $model->message = 'Test message';
        $model->message_id = 'msg_123';
        $model->status = 'pending';
        $model->is_read = false;
        $model->created_at = '2023-01-01 10:00:00';
        $model->updated_at = '2023-01-01 11:00:00';

        MessageModel::shouldReceive('find')
            ->once()
            ->with(1)
            ->andReturn($model);

        $result = $this->repository->findById(1);

        $this->assertInstanceOf(Message::class, $result);
        $this->assertEquals(1, $result->getId());
        $this->assertEquals('John Doe', $result->getName());
        $this->assertEquals('john@example.com', $result->getEmail()->getValue());
    }

    public function testFindByIdReturnsNullWhenNotFound(): void
    {
        MessageModel::shouldReceive('find')
            ->once()
            ->with(999)
            ->andReturn(null);

        $result = $this->repository->findById(999);

        $this->assertNull($result);
    }

    public function testFindByMessageIdReturnsMessageWhenFound(): void
    {
        $model = m::mock(MessageModel::class);
        $model->id = 1;
        $model->name = 'John Doe';
        $model->email = 'john@example.com';
        $model->subject = 'Test Subject';
        $model->message = 'Test message';
        $model->message_id = 'msg_123';
        $model->status = 'pending';
        $model->is_read = false;
        $model->created_at = '2023-01-01 10:00:00';
        $model->updated_at = '2023-01-01 11:00:00';

        $queryBuilder = m::mock();
        $queryBuilder->shouldReceive('first')
            ->once()
            ->andReturn($model);

        MessageModel::shouldReceive('where')
            ->once()
            ->with('message_id', 'msg_123')
            ->andReturn($queryBuilder);

        $result = $this->repository->findByMessageId('msg_123');

        $this->assertInstanceOf(Message::class, $result);
        $this->assertEquals('msg_123', $result->getMessageId());
    }

    public function testFindByMessageIdReturnsNullWhenNotFound(): void
    {
        $queryBuilder = m::mock();
        $queryBuilder->shouldReceive('first')
            ->once()
            ->andReturn(null);

        MessageModel::shouldReceive('where')
            ->once()
            ->with('message_id', 'nonexistent')
            ->andReturn($queryBuilder);

        $result = $this->repository->findByMessageId('nonexistent');

        $this->assertNull($result);
    }

    public function testGetPaginatedReturnsPaginatorWithEntities(): void
    {
        // Create mock models
        $model1 = m::mock(MessageModel::class);
        $model1->id = 1;
        $model1->name = 'John Doe';
        $model1->email = 'john@example.com';
        $model1->subject = 'Subject 1';
        $model1->message = 'Message 1';
        $model1->message_id = null;
        $model1->status = 'pending';
        $model1->is_read = false;
        $model1->created_at = '2023-01-01 10:00:00';
        $model1->updated_at = '2023-01-01 11:00:00';

        $model2 = m::mock(MessageModel::class);
        $model2->id = 2;
        $model2->name = 'Jane Doe';
        $model2->email = 'jane@example.com';
        $model2->subject = 'Subject 2';
        $model2->message = 'Message 2';
        $model2->message_id = null;
        $model2->status = 'delivered';
        $model2->is_read = true;
        $model2->created_at = '2023-01-01 12:00:00';
        $model2->updated_at = '2023-01-01 13:00:00';

        $collection = new Collection([$model1, $model2]);

        // Create mock paginator
        $paginator = m::mock(LengthAwarePaginator::class);
        $paginator->shouldReceive('getCollection')
            ->once()
            ->andReturn($collection);
        $paginator->shouldReceive('total')
            ->once()
            ->andReturn(2);
        $paginator->shouldReceive('perPage')
            ->once()
            ->andReturn(10);
        $paginator->shouldReceive('currentPage')
            ->once()
            ->andReturn(1);
        $paginator->shouldReceive('path')
            ->once()
            ->andReturn('/api/messages');
        $paginator->shouldReceive('getPageName')
            ->once()
            ->andReturn('page');

        // Mock query builder
        $queryBuilder = m::mock();
        $queryBuilder->shouldReceive('where')
            ->andReturnSelf();
        $queryBuilder->shouldReceive('orderBy')
            ->once()
            ->with('created_at', 'desc')
            ->andReturnSelf();
        $queryBuilder->shouldReceive('paginate')
            ->once()
            ->with(10, ['*'], 'page', 1)
            ->andReturn($paginator);

        MessageModel::shouldReceive('query')
            ->once()
            ->andReturn($queryBuilder);

        $result = $this->repository->getPaginated(1, 10, [], 'created_at', 'desc');

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals(2, $result->total());
        $this->assertEquals(10, $result->perPage());
        $this->assertEquals(1, $result->currentPage());
    }

    public function testGetPaginatedWithFilters(): void
    {
        $filters = [
            'is_read' => true,
            'status' => 'delivered',
            'search' => 'test query'
        ];

        $paginator = m::mock(LengthAwarePaginator::class);
        $paginator->shouldReceive('getCollection')
            ->once()
            ->andReturn(new Collection());
        $paginator->shouldReceive('total')->once()->andReturn(0);
        $paginator->shouldReceive('perPage')->once()->andReturn(10);
        $paginator->shouldReceive('currentPage')->once()->andReturn(1);
        $paginator->shouldReceive('path')->once()->andReturn('/api/messages');
        $paginator->shouldReceive('getPageName')->once()->andReturn('page');

        $queryBuilder = m::mock();
        $queryBuilder->shouldReceive('where')
            ->with('is_read', true)
            ->once()
            ->andReturnSelf();
        $queryBuilder->shouldReceive('where')
            ->with('status', 'delivered')
            ->once()
            ->andReturnSelf();

        $queryBuilder->shouldReceive('where')
            ->once()
            ->andReturnUsing(function ($callback) use ($queryBuilder) {
                $subQuery = m::mock();
                $subQuery->shouldReceive('where')
                    ->with('name', 'LIKE', '%test query%')
                    ->once()
                    ->andReturnSelf();
                $subQuery->shouldReceive('orWhere')
                    ->with('email', 'LIKE', '%test query%')
                    ->once()
                    ->andReturnSelf();
                $subQuery->shouldReceive('orWhere')
                    ->with('subject', 'LIKE', '%test query%')
                    ->once()
                    ->andReturnSelf();
                $subQuery->shouldReceive('orWhere')
                    ->with('message', 'LIKE', '%test query%')
                    ->once()
                    ->andReturnSelf();

                $callback($subQuery);
                return $queryBuilder;
            });

        $queryBuilder->shouldReceive('orderBy')
            ->once()
            ->with('created_at', 'desc')
            ->andReturnSelf();
        $queryBuilder->shouldReceive('paginate')
            ->once()
            ->andReturn($paginator);

        MessageModel::shouldReceive('query')
            ->once()
            ->andReturn($queryBuilder);

        $result = $this->repository->getPaginated(1, 10, $filters);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
    }

    public function testCreateReturnsMessage(): void
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'subject' => 'Test Subject',
            'message' => 'Test message content',
            'message_id' => 'msg_123',
            'status' => 'pending',
            'is_read' => false,
        ];

        $model = m::mock(MessageModel::class);
        $model->id = 1;
        $model->name = 'John Doe';
        $model->email = 'john@example.com';
        $model->subject = 'Test Subject';
        $model->message = 'Test message content';
        $model->message_id = 'msg_123';
        $model->status = 'pending';
        $model->is_read = false;
        $model->created_at = '2023-01-01 10:00:00';
        $model->updated_at = '2023-01-01 10:00:00';

        MessageModel::shouldReceive('create')
            ->once()
            ->with([
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'subject' => 'Test Subject',
                'message' => 'Test message content',
                'message_id' => 'msg_123',
                'status' => 'pending',
                'is_read' => false,
            ])
            ->andReturn($model);

        $result = $this->repository->create($data);

        $this->assertInstanceOf(Message::class, $result);
        $this->assertEquals('John Doe', $result->getName());
        $this->assertEquals('john@example.com', $result->getEmail()->getValue());
    }

    public function testCreateWithDefaults(): void
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'subject' => 'Test Subject',
            'message' => 'Test message content',
        ];

        $model = m::mock(MessageModel::class);
        $model->id = 1;
        $model->name = 'John Doe';
        $model->email = 'john@example.com';
        $model->subject = 'Test Subject';
        $model->message = 'Test message content';
        $model->message_id = null;
        $model->status = 'pending';
        $model->is_read = false;
        $model->created_at = '2023-01-01 10:00:00';
        $model->updated_at = '2023-01-01 10:00:00';

        MessageModel::shouldReceive('create')
            ->once()
            ->with([
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'subject' => 'Test Subject',
                'message' => 'Test message content',
                'message_id' => null,
                'status' => 'pending',
                'is_read' => false,
            ])
            ->andReturn($model);

        $result = $this->repository->create($data);

        $this->assertInstanceOf(Message::class, $result);
        $this->assertNull($result->getMessageId());
        $this->assertEquals(MessageStatus::pending(), $result->getStatus());
        $this->assertFalse($result->isRead());
    }

    public function testUpdateReturnsTrueWhenSuccessful(): void
    {
        $model = m::mock(MessageModel::class);
        $model->shouldReceive('update')
            ->once()
            ->with(['is_read' => true])
            ->andReturn(true);

        MessageModel::shouldReceive('find')
            ->once()
            ->with(1)
            ->andReturn($model);

        $result = $this->repository->update(1, ['is_read' => true]);

        $this->assertTrue($result);
    }

    public function testUpdateReturnsFalseWhenModelNotFound(): void
    {
        MessageModel::shouldReceive('find')
            ->once()
            ->with(999)
            ->andReturn(null);

        $result = $this->repository->update(999, ['is_read' => true]);

        $this->assertFalse($result);
    }

    public function testDeleteReturnsTrueWhenSuccessful(): void
    {
        $model = m::mock(MessageModel::class);
        $model->shouldReceive('delete')
            ->once()
            ->andReturn(true);

        MessageModel::shouldReceive('find')
            ->once()
            ->with(1)
            ->andReturn($model);

        $result = $this->repository->delete(1);

        $this->assertTrue($result);
    }

    public function testDeleteReturnsFalseWhenModelNotFound(): void
    {
        MessageModel::shouldReceive('find')
            ->once()
            ->with(999)
            ->andReturn(null);

        $result = $this->repository->delete(999);

        $this->assertFalse($result);
    }

    public function testMarkAsReadReturnsTrueWhenSuccessful(): void
    {
        $model = m::mock(MessageModel::class);
        $model->shouldReceive('update')
            ->once()
            ->with(['is_read' => true])
            ->andReturn(true);

        MessageModel::shouldReceive('find')
            ->once()
            ->with(1)
            ->andReturn($model);

        $result = $this->repository->markAsRead(1);

        $this->assertTrue($result);
    }

    public function testCountReturnsTotalCount(): void
    {
        $queryBuilder = m::mock();
        $queryBuilder->shouldReceive('count')
            ->once()
            ->andReturn(42);

        MessageModel::shouldReceive('query')
            ->once()
            ->andReturn($queryBuilder);

        $result = $this->repository->count();

        $this->assertEquals(42, $result);
    }

    public function testCountWithFilters(): void
    {
        $filters = [
            'is_read' => true,
            'status' => 'delivered'
        ];

        $queryBuilder = m::mock();
        $queryBuilder->shouldReceive('where')
            ->with('is_read', true)
            ->once()
            ->andReturnSelf();
        $queryBuilder->shouldReceive('where')
            ->with('status', 'delivered')
            ->once()
            ->andReturnSelf();
        $queryBuilder->shouldReceive('count')
            ->once()
            ->andReturn(15);

        MessageModel::shouldReceive('query')
            ->once()
            ->andReturn($queryBuilder);

        $result = $this->repository->count($filters);

        $this->assertEquals(15, $result);
    }

    public function testSearchReturnsArrayOfMessages(): void
    {
        $model = m::mock(MessageModel::class);
        $model->id = 1;
        $model->name = 'John Doe';
        $model->email = 'john@example.com';
        $model->subject = 'Test Subject';
        $model->message = 'Test message';
        $model->message_id = null;
        $model->status = 'pending';
        $model->is_read = false;
        $model->created_at = '2023-01-01 10:00:00';
        $model->updated_at = '2023-01-01 11:00:00';

        $collection = m::mock(Collection::class);
        $collection->shouldReceive('map')
            ->once()
            ->andReturnUsing(function ($callback) {
                $message = $callback((object) [
                    'id' => 1,
                    'name' => 'John Doe',
                    'email' => 'john@example.com',
                    'subject' => 'Test Subject',
                    'message' => 'Test message',
                    'message_id' => null,
                    'status' => 'pending',
                    'is_read' => false,
                    'created_at' => '2023-01-01 10:00:00',
                    'updated_at' => '2023-01-01 11:00:00',
                ]);
                return [$message];
            });
        $collection->shouldReceive('toArray')
            ->once()
            ->andReturn([]);

        $queryBuilder = m::mock();
        $queryBuilder->shouldReceive('where')
            ->once()
            ->andReturnUsing(function ($callback) use ($queryBuilder) {
                $subQuery = m::mock();
                $subQuery->shouldReceive('where')
                    ->with('name', 'LIKE', '%test%')
                    ->once()
                    ->andReturnSelf();
                $subQuery->shouldReceive('orWhere')
                    ->with('email', 'LIKE', '%test%')
                    ->once()
                    ->andReturnSelf();
                $subQuery->shouldReceive('orWhere')
                    ->with('subject', 'LIKE', '%test%')
                    ->once()
                    ->andReturnSelf();
                $subQuery->shouldReceive('orWhere')
                    ->with('message', 'LIKE', '%test%')
                    ->once()
                    ->andReturnSelf();

                $callback($subQuery);
                return $queryBuilder;
            });
        $queryBuilder->shouldReceive('get')
            ->once()
            ->andReturn($collection);

        MessageModel::shouldReceive('where')
            ->once()
            ->andReturn($queryBuilder);

        $result = $this->repository->search('test');

        $this->assertIsArray($result);
    }

    public function testSearchWithFilters(): void
    {
        $filters = [
            'is_read' => true,
            'status' => 'delivered'
        ];

        $collection = m::mock(Collection::class);
        $collection->shouldReceive('map')
            ->once()
            ->andReturn([]);
        $collection->shouldReceive('toArray')
            ->once()
            ->andReturn([]);

        $queryBuilder = m::mock();
        $queryBuilder->shouldReceive('where')
            ->once()
            ->andReturnUsing(function ($callback) use ($queryBuilder) {
                $subQuery = m::mock();
                $subQuery->shouldReceive('where')
                    ->with('name', 'LIKE', '%search%')
                    ->once()
                    ->andReturnSelf();
                $subQuery->shouldReceive('orWhere')
                    ->with('email', 'LIKE', '%search%')
                    ->once()
                    ->andReturnSelf();
                $subQuery->shouldReceive('orWhere')
                    ->with('subject', 'LIKE', '%search%')
                    ->once()
                    ->andReturnSelf();
                $subQuery->shouldReceive('orWhere')
                    ->with('message', 'LIKE', '%search%')
                    ->once()
                    ->andReturnSelf();

                $callback($subQuery);
                return $queryBuilder;
            });
        $queryBuilder->shouldReceive('where')
            ->with('is_read', true)
            ->once()
            ->andReturnSelf();
        $queryBuilder->shouldReceive('where')
            ->with('status', 'delivered')
            ->once()
            ->andReturnSelf();
        $queryBuilder->shouldReceive('get')
            ->once()
            ->andReturn($collection);

        MessageModel::shouldReceive('where')
            ->once()
            ->andReturn($queryBuilder);

        $result = $this->repository->search('search', $filters);

        $this->assertIsArray($result);
    }
}