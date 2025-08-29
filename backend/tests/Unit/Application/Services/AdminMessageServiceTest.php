<?php

namespace App\Tests\Unit\Application\Services;

use App\Application\Services\AdminMessageService;
use App\Domain\Interfaces\MessageRepositoryInterface;
use App\Domain\Interfaces\AuthenticationServiceInterface;
use App\Domain\Interfaces\ValidationInterface;
use App\Domain\Entities\Message;
use Illuminate\Pagination\LengthAwarePaginator;
use PHPUnit\Framework\TestCase;
use Mockery as m;

class AdminMessageServiceTest extends TestCase
{
    private AdminMessageService $service;
    private MessageRepositoryInterface $messageRepository;
    private AuthenticationServiceInterface $authService;
    private ValidationInterface $validator;

    protected function setUp(): void
    {
        $this->messageRepository = m::mock(MessageRepositoryInterface::class);
        $this->authService = m::mock(AuthenticationServiceInterface::class);
        $this->validator = m::mock(ValidationInterface::class);

        $this->service = new AdminMessageService(
            $this->messageRepository,
            $this->authService,
            $this->validator
        );
    }

    protected function tearDown(): void
    {
        m::close();
    }

    public function testConstructor(): void
    {
        $this->assertInstanceOf(AdminMessageService::class, $this->service);
    }

    public function testGetMessagesWhenNotAuthenticated(): void
    {
        $this->authService->shouldReceive('isAuthenticated')
            ->once()
            ->andReturn(false);

        $result = $this->service->getMessages();

        $this->assertEquals([
            'success' => false,
            'message' => 'Unauthorized'
        ], $result);
    }

    public function testGetMessagesSuccess(): void
    {
        $params = ['page' => 1, 'limit' => 10];
        $message = m::mock(Message::class);
        $message->shouldReceive('toArray')
            ->once()
            ->andReturn(['id' => 1, 'name' => 'Test']);

        $paginator = m::mock(LengthAwarePaginator::class);
        $paginator->shouldReceive('items')
            ->once()
            ->andReturn([$message]);
        $paginator->shouldReceive('total')->once()->andReturn(1);
        $paginator->shouldReceive('perPage')->once()->andReturn(10);
        $paginator->shouldReceive('currentPage')->once()->andReturn(1);
        $paginator->shouldReceive('lastPage')->once()->andReturn(1);
        $paginator->shouldReceive('firstItem')->once()->andReturn(1);
        $paginator->shouldReceive('lastItem')->once()->andReturn(1);

        $this->authService->shouldReceive('isAuthenticated')
            ->once()
            ->andReturn(true);

        $this->validator->shouldReceive('validatePagination')
            ->once()
            ->with($params)
            ->andReturn(true);

        $this->validator->shouldReceive('isValid')
            ->once()
            ->andReturn(true);

        $this->messageRepository->shouldReceive('getPaginated')
            ->once()
            ->with(1, 10, [], 'created_at', 'desc')
            ->andReturn($paginator);

        $result = $this->service->getMessages($params);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('pagination', $result);
    }

    public function testGetMessageSuccess(): void
    {
        $message = m::mock(Message::class);
        $message->shouldReceive('isRead')
            ->once()
            ->andReturn(false);
        $message->shouldReceive('toArray')
            ->twice()
            ->andReturn(['id' => 1, 'name' => 'Test']);

        $this->authService->shouldReceive('isAuthenticated')
            ->once()
            ->andReturn(true);

        $this->messageRepository->shouldReceive('findById')
            ->twice()
            ->with(1)
            ->andReturn($message);

        $this->messageRepository->shouldReceive('markAsRead')
            ->once()
            ->with(1)
            ->andReturn(true);

        $result = $this->service->getMessage(1);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('data', $result);
    }

    public function testBulkActionMarkRead(): void
    {
        $this->authService->shouldReceive('isAuthenticated')
            ->once()
            ->andReturn(true);

        $this->validator->shouldReceive('validateBulkAction')
            ->once()
            ->andReturn(true);

        $this->validator->shouldReceive('isValid')
            ->once()
            ->andReturn(true);

        $this->messageRepository->shouldReceive('markAsRead')
            ->with(1)
            ->once()
            ->andReturn(true);

        $result = $this->service->bulkAction('mark_read', [1]);

        $this->assertTrue($result['success']);
        $this->assertEquals(1, $result['affected_rows']);
    }

    public function testGetStatisticsSuccess(): void
    {
        $this->authService->shouldReceive('isAuthenticated')
            ->once()
            ->andReturn(true);

        $this->messageRepository->shouldReceive('count')
            ->with()
            ->once()
            ->andReturn(100);

        $this->messageRepository->shouldReceive('count')
            ->with(['is_read' => false])
            ->once()
            ->andReturn(25);

        $result = $this->service->getStatistics();

        $this->assertTrue($result['success']);
        $this->assertEquals([
            'total' => 100,
            'read' => 75,
            'unread' => 25
        ], $result['statistics']);
    }
}
