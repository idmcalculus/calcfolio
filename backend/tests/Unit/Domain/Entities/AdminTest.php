<?php

namespace App\Tests\Unit\Domain\Entities;

use App\Domain\Entities\Admin;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class AdminTest extends TestCase
{
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;

    protected function setUp(): void
    {
        $this->createdAt = new DateTimeImmutable('2023-01-01 10:00:00');
        $this->updatedAt = new DateTimeImmutable('2023-01-01 11:00:00');
    }

    public function testConstructorWithAllParameters(): void
    {
        $admin = new Admin(
            1,
            'adminuser',
            '$2y$10$hashedpassword',
            $this->createdAt,
            $this->updatedAt
        );

        $this->assertEquals(1, $admin->getId());
        $this->assertEquals('adminuser', $admin->getUsername());
        $this->assertEquals('$2y$10$hashedpassword', $admin->getPasswordHash());
        $this->assertEquals($this->createdAt, $admin->getCreatedAt());
        $this->assertEquals($this->updatedAt, $admin->getUpdatedAt());
    }

    public function testConstructorWithDefaults(): void
    {
        $admin = new Admin(
            1,
            'adminuser',
            '$2y$10$hashedpassword'
        );

        $this->assertEquals(1, $admin->getId());
        $this->assertEquals('adminuser', $admin->getUsername());
        $this->assertEquals('$2y$10$hashedpassword', $admin->getPasswordHash());
        $this->assertInstanceOf(DateTimeImmutable::class, $admin->getCreatedAt());
        $this->assertInstanceOf(DateTimeImmutable::class, $admin->getUpdatedAt());
    }

    public function testGetters(): void
    {
        $admin = new Admin(
            1,
            'adminuser',
            '$2y$10$hashedpassword',
            $this->createdAt,
            $this->updatedAt
        );

        $this->assertEquals(1, $admin->getId());
        $this->assertEquals('adminuser', $admin->getUsername());
        $this->assertEquals('$2y$10$hashedpassword', $admin->getPasswordHash());
        $this->assertEquals($this->createdAt, $admin->getCreatedAt());
        $this->assertEquals($this->updatedAt, $admin->getUpdatedAt());
    }

    public function testVerifyPasswordWithValidPassword(): void
    {
        $password = 'correctpassword';
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $admin = new Admin(1, 'adminuser', $hash);

        $this->assertTrue($admin->verifyPassword($password));
    }

    public function testVerifyPasswordWithInvalidPassword(): void
    {
        $hash = password_hash('correctpassword', PASSWORD_DEFAULT);

        $admin = new Admin(1, 'adminuser', $hash);

        $this->assertFalse($admin->verifyPassword('wrongpassword'));
    }

    public function testVerifyPasswordWithEmptyPassword(): void
    {
        $hash = password_hash('correctpassword', PASSWORD_DEFAULT);

        $admin = new Admin(1, 'adminuser', $hash);

        $this->assertFalse($admin->verifyPassword(''));
    }

    public function testUpdatePassword(): void
    {
        $admin = new Admin(
            1,
            'adminuser',
            '$2y$10$oldhash',
            $this->createdAt,
            $this->updatedAt
        );

        $this->assertEquals('$2y$10$oldhash', $admin->getPasswordHash());
        $this->assertEquals($this->updatedAt, $admin->getUpdatedAt());

        $newHash = '$2y$10$newhash';
        $admin->updatePassword($newHash);

        $this->assertEquals($newHash, $admin->getPasswordHash());
        $this->assertNotEquals($this->updatedAt, $admin->getUpdatedAt());
        $this->assertInstanceOf(DateTimeImmutable::class, $admin->getUpdatedAt());
    }

    public function testToArray(): void
    {
        $admin = new Admin(
            1,
            'adminuser',
            '$2y$10$hashedpassword',
            $this->createdAt,
            $this->updatedAt
        );

        $expected = [
            'id' => 1,
            'username' => 'adminuser',
            'created_at' => $this->createdAt->format('c'),
            'updated_at' => $this->updatedAt->format('c'),
        ];

        $this->assertEquals($expected, $admin->toArray());
    }

    public function testToArrayWithDefaultTimestamps(): void
    {
        $admin = new Admin(
            1,
            'adminuser',
            '$2y$10$hashedpassword'
        );

        $array = $admin->toArray();

        $this->assertIsString($array['created_at']);
        $this->assertIsString($array['updated_at']);
        $this->assertNotEmpty($array['created_at']);
        $this->assertNotEmpty($array['updated_at']);
    }

    public function testUsernameWithSpecialCharacters(): void
    {
        $admin = new Admin(
            1,
            'admin_user.test@example',
            '$2y$10$hashedpassword'
        );

        $this->assertEquals('admin_user.test@example', $admin->getUsername());
        $this->assertEquals('admin_user.test@example', $admin->toArray()['username']);
    }

    public function testPasswordHashWithDifferentAlgorithms(): void
    {
        // Test with bcrypt hash
        $bcryptHash = '$2y$10$abcdefghijklmnopqrstuv';
        $admin1 = new Admin(1, 'admin1', $bcryptHash);
        $this->assertEquals($bcryptHash, $admin1->getPasswordHash());

        // Test with argon2 hash
        $argon2Hash = '$argon2i$v=19$m=65536,t=4,p=1$abcdefghijk';
        $admin2 = new Admin(2, 'admin2', $argon2Hash);
        $this->assertEquals($argon2Hash, $admin2->getPasswordHash());
    }

    public function testTimestampsAreImmutable(): void
    {
        $originalCreatedAt = $this->createdAt;
        $originalUpdatedAt = $this->updatedAt;

        $admin = new Admin(
            1,
            'adminuser',
            '$2y$10$hashedpassword',
            $originalCreatedAt,
            $originalUpdatedAt
        );

        // Original timestamps should not be modified
        $this->assertSame($originalCreatedAt, $admin->getCreatedAt());
        $this->assertSame($originalUpdatedAt, $admin->getUpdatedAt());

        // Operations should create new timestamps
        $admin->updatePassword('$2y$10$newhash');
        $this->assertNotSame($originalUpdatedAt, $admin->getUpdatedAt());
        $this->assertSame($originalCreatedAt, $admin->getCreatedAt()); // CreatedAt should not change
    }

    public function testPasswordVerificationWithVariousInputs(): void
    {
        $password = 'MySecureP@ssw0rd123!';
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $admin = new Admin(1, 'adminuser', $hash);

        // Test with correct password
        $this->assertTrue($admin->verifyPassword($password));

        // Test with wrong password
        $this->assertFalse($admin->verifyPassword('wrongpassword'));

        // Test with password that looks similar
        $this->assertFalse($admin->verifyPassword('MySecureP@ssw0rd123')); // missing !

        // Test with empty password
        $this->assertFalse($admin->verifyPassword(''));

        // Test with very long password
        $longPassword = str_repeat('a', 1000);
        $this->assertFalse($admin->verifyPassword($longPassword));
    }

    public function testAdminWithNumericId(): void
    {
        $admin = new Admin(
            999,
            'admin999',
            '$2y$10$hashedpassword'
        );

        $this->assertEquals(999, $admin->getId());
        $this->assertEquals(999, $admin->toArray()['id']);
    }

    public function testAdminWithZeroId(): void
    {
        $admin = new Admin(
            0,
            'admin0',
            '$2y$10$hashedpassword'
        );

        $this->assertEquals(0, $admin->getId());
        $this->assertEquals(0, $admin->toArray()['id']);
    }

    public function testAdminWithNegativeId(): void
    {
        $admin = new Admin(
            -1,
            'admin-1',
            '$2y$10$hashedpassword'
        );

        $this->assertEquals(-1, $admin->getId());
        $this->assertEquals(-1, $admin->toArray()['id']);
    }
}