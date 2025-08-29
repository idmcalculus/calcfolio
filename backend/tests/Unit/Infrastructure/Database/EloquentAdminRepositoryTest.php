<?php

namespace App\Tests\Unit\Infrastructure\Database;

use App\Domain\Entities\Admin;
use App\Infrastructure\Database\EloquentAdminRepository;
use App\Models\Admin as AdminModel;
use Illuminate\Database\Eloquent\Collection;
use PHPUnit\Framework\TestCase;
use Mockery as m;
use DateTimeImmutable;

class EloquentAdminRepositoryTest extends TestCase
{
    private EloquentAdminRepository $repository;

    protected function setUp(): void
    {
        $this->repository = new EloquentAdminRepository();
    }

    protected function tearDown(): void
    {
        m::close();
    }

    public function testFindByIdReturnsAdminWhenFound(): void
    {
        $model = m::mock(AdminModel::class);
        $model->id = 1;
        $model->username = 'adminuser';
        $model->password_hash = '$2y$10$hashedpassword';
        $model->created_at = '2023-01-01 10:00:00';
        $model->updated_at = '2023-01-01 11:00:00';

        AdminModel::shouldReceive('find')
            ->once()
            ->with(1)
            ->andReturn($model);

        $result = $this->repository->findById(1);

        $this->assertInstanceOf(Admin::class, $result);
        $this->assertEquals(1, $result->getId());
        $this->assertEquals('adminuser', $result->getUsername());
        $this->assertEquals('$2y$10$hashedpassword', $result->getPasswordHash());
    }

    public function testFindByIdReturnsNullWhenNotFound(): void
    {
        AdminModel::shouldReceive('find')
            ->once()
            ->with(999)
            ->andReturn(null);

        $result = $this->repository->findById(999);

        $this->assertNull($result);
    }

    public function testFindByUsernameReturnsAdminWhenFound(): void
    {
        $model = m::mock(AdminModel::class);
        $model->id = 1;
        $model->username = 'adminuser';
        $model->password_hash = '$2y$10$hashedpassword';
        $model->created_at = '2023-01-01 10:00:00';
        $model->updated_at = '2023-01-01 11:00:00';

        $queryBuilder = m::mock();
        $queryBuilder->shouldReceive('first')
            ->once()
            ->andReturn($model);

        AdminModel::shouldReceive('where')
            ->once()
            ->with('username', 'adminuser')
            ->andReturn($queryBuilder);

        $result = $this->repository->findByUsername('adminuser');

        $this->assertInstanceOf(Admin::class, $result);
        $this->assertEquals('adminuser', $result->getUsername());
    }

    public function testFindByUsernameReturnsNullWhenNotFound(): void
    {
        $queryBuilder = m::mock();
        $queryBuilder->shouldReceive('first')
            ->once()
            ->andReturn(null);

        AdminModel::shouldReceive('where')
            ->once()
            ->with('username', 'nonexistent')
            ->andReturn($queryBuilder);

        $result = $this->repository->findByUsername('nonexistent');

        $this->assertNull($result);
    }

    public function testCreateReturnsAdmin(): void
    {
        $data = [
            'username' => 'newadmin',
            'password_hash' => '$2y$10$newhashedpassword',
        ];

        $model = m::mock(AdminModel::class);
        $model->id = 1;
        $model->username = 'newadmin';
        $model->password_hash = '$2y$10$newhashedpassword';
        $model->created_at = '2023-01-01 10:00:00';
        $model->updated_at = '2023-01-01 10:00:00';

        AdminModel::shouldReceive('create')
            ->once()
            ->with([
                'username' => 'newadmin',
                'password_hash' => '$2y$10$newhashedpassword',
            ])
            ->andReturn($model);

        $result = $this->repository->create($data);

        $this->assertInstanceOf(Admin::class, $result);
        $this->assertEquals('newadmin', $result->getUsername());
        $this->assertEquals('$2y$10$newhashedpassword', $result->getPasswordHash());
    }

    public function testUpdateReturnsTrueWhenSuccessful(): void
    {
        $model = m::mock(AdminModel::class);
        $model->shouldReceive('update')
            ->once()
            ->with(['username' => 'updatedadmin'])
            ->andReturn(true);

        AdminModel::shouldReceive('find')
            ->once()
            ->with(1)
            ->andReturn($model);

        $result = $this->repository->update(1, ['username' => 'updatedadmin']);

        $this->assertTrue($result);
    }

    public function testUpdateReturnsFalseWhenModelNotFound(): void
    {
        AdminModel::shouldReceive('find')
            ->once()
            ->with(999)
            ->andReturn(null);

        $result = $this->repository->update(999, ['username' => 'updatedadmin']);

        $this->assertFalse($result);
    }

    public function testDeleteReturnsTrueWhenSuccessful(): void
    {
        $model = m::mock(AdminModel::class);
        $model->shouldReceive('delete')
            ->once()
            ->andReturn(true);

        AdminModel::shouldReceive('find')
            ->once()
            ->with(1)
            ->andReturn($model);

        $result = $this->repository->delete(1);

        $this->assertTrue($result);
    }

    public function testDeleteReturnsFalseWhenModelNotFound(): void
    {
        AdminModel::shouldReceive('find')
            ->once()
            ->with(999)
            ->andReturn(null);

        $result = $this->repository->delete(999);

        $this->assertFalse($result);
    }

    public function testUsernameExistsReturnsTrueWhenExists(): void
    {
        $queryBuilder = m::mock();
        $queryBuilder->shouldReceive('exists')
            ->once()
            ->andReturn(true);

        AdminModel::shouldReceive('where')
            ->once()
            ->with('username', 'existinguser')
            ->andReturn($queryBuilder);

        $result = $this->repository->usernameExists('existinguser');

        $this->assertTrue($result);
    }

    public function testUsernameExistsReturnsFalseWhenNotExists(): void
    {
        $queryBuilder = m::mock();
        $queryBuilder->shouldReceive('exists')
            ->once()
            ->andReturn(false);

        AdminModel::shouldReceive('where')
            ->once()
            ->with('username', 'nonexistent')
            ->andReturn($queryBuilder);

        $result = $this->repository->usernameExists('nonexistent');

        $this->assertFalse($result);
    }

    public function testUsernameExistsWithExcludeId(): void
    {
        $queryBuilder = m::mock();
        $queryBuilder->shouldReceive('where')
            ->once()
            ->with('id', '!=', 1)
            ->andReturnSelf();
        $queryBuilder->shouldReceive('exists')
            ->once()
            ->andReturn(false);

        AdminModel::shouldReceive('where')
            ->once()
            ->with('username', 'adminuser')
            ->andReturn($queryBuilder);

        $result = $this->repository->usernameExists('adminuser', 1);

        $this->assertFalse($result);
    }

    public function testGetAllReturnsArrayOfAdmins(): void
    {
        $model1 = m::mock(AdminModel::class);
        $model1->id = 1;
        $model1->username = 'admin1';
        $model1->password_hash = '$2y$10$hash1';
        $model1->created_at = '2023-01-01 10:00:00';
        $model1->updated_at = '2023-01-01 11:00:00';

        $model2 = m::mock(AdminModel::class);
        $model2->id = 2;
        $model2->username = 'admin2';
        $model2->password_hash = '$2y$10$hash2';
        $model2->created_at = '2023-01-01 12:00:00';
        $model2->updated_at = '2023-01-01 13:00:00';

        $collection = m::mock(Collection::class);
        $collection->shouldReceive('map')
            ->once()
            ->andReturnUsing(function ($callback) {
                $admin1 = $callback((object) [
                    'id' => 1,
                    'username' => 'admin1',
                    'password_hash' => '$2y$10$hash1',
                    'created_at' => '2023-01-01 10:00:00',
                    'updated_at' => '2023-01-01 11:00:00',
                ]);
                $admin2 = $callback((object) [
                    'id' => 2,
                    'username' => 'admin2',
                    'password_hash' => '$2y$10$hash2',
                    'created_at' => '2023-01-01 12:00:00',
                    'updated_at' => '2023-01-01 13:00:00',
                ]);
                return [$admin1, $admin2];
            });
        $collection->shouldReceive('toArray')
            ->once()
            ->andReturn([]);

        AdminModel::shouldReceive('all')
            ->once()
            ->andReturn($collection);

        $result = $this->repository->getAll();

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
    }

    public function testGetAllReturnsEmptyArrayWhenNoAdmins(): void
    {
        $collection = m::mock(Collection::class);
        $collection->shouldReceive('map')
            ->once()
            ->andReturn([]);
        $collection->shouldReceive('toArray')
            ->once()
            ->andReturn([]);

        AdminModel::shouldReceive('all')
            ->once()
            ->andReturn($collection);

        $result = $this->repository->getAll();

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testModelToEntityConvertsCorrectly(): void
    {
        $model = m::mock(AdminModel::class);
        $model->id = 1;
        $model->username = 'testadmin';
        $model->password_hash = '$2y$10$testpassword';
        $model->created_at = '2023-01-01 10:00:00';
        $model->updated_at = '2023-01-01 11:00:00';

        AdminModel::shouldReceive('find')
            ->once()
            ->with(1)
            ->andReturn($model);

        $result = $this->repository->findById(1);

        $this->assertInstanceOf(Admin::class, $result);
        $this->assertEquals(1, $result->getId());
        $this->assertEquals('testadmin', $result->getUsername());
        $this->assertEquals('$2y$10$testpassword', $result->getPasswordHash());
        $this->assertInstanceOf(DateTimeImmutable::class, $result->getCreatedAt());
        $this->assertInstanceOf(DateTimeImmutable::class, $result->getUpdatedAt());
    }

    public function testUsernameExistsHandlesNullExcludeId(): void
    {
        $queryBuilder = m::mock();
        $queryBuilder->shouldReceive('exists')
            ->once()
            ->andReturn(true);

        AdminModel::shouldReceive('where')
            ->once()
            ->with('username', 'testuser')
            ->andReturn($queryBuilder);

        $result = $this->repository->usernameExists('testuser', null);

        $this->assertTrue($result);
    }

    public function testUsernameExistsWithZeroExcludeId(): void
    {
        $queryBuilder = m::mock();
        $queryBuilder->shouldReceive('where')
            ->once()
            ->with('id', '!=', 0)
            ->andReturnSelf();
        $queryBuilder->shouldReceive('exists')
            ->once()
            ->andReturn(false);

        AdminModel::shouldReceive('where')
            ->once()
            ->with('username', 'testuser')
            ->andReturn($queryBuilder);

        $result = $this->repository->usernameExists('testuser', 0);

        $this->assertFalse($result);
    }

    public function testCreateWithMinimalData(): void
    {
        $data = [
            'username' => 'minimaladmin',
            'password_hash' => '$2y$10$minimalhash',
        ];

        $model = m::mock(AdminModel::class);
        $model->id = 1;
        $model->username = 'minimaladmin';
        $model->password_hash = '$2y$10$minimalhash';
        $model->created_at = '2023-01-01 10:00:00';
        $model->updated_at = '2023-01-01 10:00:00';

        AdminModel::shouldReceive('create')
            ->once()
            ->with($data)
            ->andReturn($model);

        $result = $this->repository->create($data);

        $this->assertInstanceOf(Admin::class, $result);
        $this->assertEquals('minimaladmin', $result->getUsername());
    }

    public function testUpdateWithMultipleFields(): void
    {
        $data = [
            'username' => 'updatedadmin',
            'password_hash' => '$2y$10$updatedhash',
        ];

        $model = m::mock(AdminModel::class);
        $model->shouldReceive('update')
            ->once()
            ->with($data)
            ->andReturn(true);

        AdminModel::shouldReceive('find')
            ->once()
            ->with(1)
            ->andReturn($model);

        $result = $this->repository->update(1, $data);

        $this->assertTrue($result);
    }
}