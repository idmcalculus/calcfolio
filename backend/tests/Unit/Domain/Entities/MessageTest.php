<?php

namespace App\Tests\Unit\Domain\Entities;

use App\Domain\Entities\Message;
use App\Domain\ValueObjects\EmailAddress;
use App\Domain\ValueObjects\MessageStatus;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class MessageTest extends TestCase
{
    private EmailAddress $email;
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;

    protected function setUp(): void
    {
        $this->email = new EmailAddress('test@example.com');
        $this->createdAt = new DateTimeImmutable('2023-01-01 10:00:00');
        $this->updatedAt = new DateTimeImmutable('2023-01-01 11:00:00');
    }

    public function testConstructorWithAllParameters(): void
    {
        $message = new Message(
            1,
            'John Doe',
            $this->email,
            'Test Subject',
            'Test message content',
            'msg_123',
            MessageStatus::pending(),
            true,
            $this->createdAt,
            $this->updatedAt
        );

        $this->assertEquals(1, $message->getId());
        $this->assertEquals('John Doe', $message->getName());
        $this->assertEquals($this->email, $message->getEmail());
        $this->assertEquals('Test Subject', $message->getSubject());
        $this->assertEquals('Test message content', $message->getMessage());
        $this->assertEquals('msg_123', $message->getMessageId());
        $this->assertEquals(MessageStatus::pending(), $message->getStatus());
        $this->assertTrue($message->isRead());
        $this->assertEquals($this->createdAt, $message->getCreatedAt());
        $this->assertEquals($this->updatedAt, $message->getUpdatedAt());
    }

    public function testConstructorWithDefaults(): void
    {
        $message = new Message(
            1,
            'John Doe',
            $this->email,
            'Test Subject',
            'Test message content'
        );

        $this->assertEquals(1, $message->getId());
        $this->assertEquals('John Doe', $message->getName());
        $this->assertEquals($this->email, $message->getEmail());
        $this->assertEquals('Test Subject', $message->getSubject());
        $this->assertEquals('Test message content', $message->getMessage());
        $this->assertNull($message->getMessageId());
        $this->assertEquals(MessageStatus::pending(), $message->getStatus());
        $this->assertFalse($message->isRead());
        $this->assertInstanceOf(DateTimeImmutable::class, $message->getCreatedAt());
        $this->assertInstanceOf(DateTimeImmutable::class, $message->getUpdatedAt());
    }

    public function testGetters(): void
    {
        $message = new Message(
            1,
            'John Doe',
            $this->email,
            'Test Subject',
            'Test message content',
            'msg_123',
            MessageStatus::delivered(),
            true,
            $this->createdAt,
            $this->updatedAt
        );

        $this->assertEquals(1, $message->getId());
        $this->assertEquals('John Doe', $message->getName());
        $this->assertEquals($this->email, $message->getEmail());
        $this->assertEquals('Test Subject', $message->getSubject());
        $this->assertEquals('Test message content', $message->getMessage());
        $this->assertEquals('msg_123', $message->getMessageId());
        $this->assertEquals(MessageStatus::delivered(), $message->getStatus());
        $this->assertTrue($message->isRead());
        $this->assertEquals($this->createdAt, $message->getCreatedAt());
        $this->assertEquals($this->updatedAt, $message->getUpdatedAt());
    }

    public function testMarkAsRead(): void
    {
        $message = new Message(
            1,
            'John Doe',
            $this->email,
            'Test Subject',
            'Test message content',
            null,
            MessageStatus::pending(),
            false,
            $this->createdAt,
            $this->updatedAt
        );

        $this->assertFalse($message->isRead());
        $this->assertEquals($this->updatedAt, $message->getUpdatedAt());

        $message->markAsRead();

        $this->assertTrue($message->isRead());
        $this->assertNotEquals($this->updatedAt, $message->getUpdatedAt());
        $this->assertInstanceOf(DateTimeImmutable::class, $message->getUpdatedAt());
    }

    public function testUpdateStatus(): void
    {
        $message = new Message(
            1,
            'John Doe',
            $this->email,
            'Test Subject',
            'Test message content',
            null,
            MessageStatus::pending(),
            false,
            $this->createdAt,
            $this->updatedAt
        );

        $this->assertEquals(MessageStatus::pending(), $message->getStatus());
        $this->assertEquals($this->updatedAt, $message->getUpdatedAt());

        $message->updateStatus(MessageStatus::delivered());

        $this->assertEquals(MessageStatus::delivered(), $message->getStatus());
        $this->assertNotEquals($this->updatedAt, $message->getUpdatedAt());
        $this->assertInstanceOf(DateTimeImmutable::class, $message->getUpdatedAt());
    }

    public function testToArray(): void
    {
        $message = new Message(
            1,
            'John Doe',
            $this->email,
            'Test Subject',
            'Test message content',
            'msg_123',
            MessageStatus::delivered(),
            true,
            $this->createdAt,
            $this->updatedAt
        );

        $expected = [
            'id' => 1,
            'name' => 'John Doe',
            'email' => 'test@example.com',
            'subject' => 'Test Subject',
            'message' => 'Test message content',
            'message_id' => 'msg_123',
            'status' => 'delivered',
            'is_read' => true,
            'created_at' => $this->createdAt->format('c'),
            'updated_at' => $this->updatedAt->format('c'),
        ];

        $this->assertEquals($expected, $message->toArray());
    }

    public function testToArrayWithNullMessageId(): void
    {
        $message = new Message(
            1,
            'John Doe',
            $this->email,
            'Test Subject',
            'Test message content',
            null,
            MessageStatus::pending(),
            false,
            $this->createdAt,
            $this->updatedAt
        );

        $array = $message->toArray();

        $this->assertNull($array['message_id']);
        $this->assertEquals('pending', $array['status']);
        $this->assertFalse($array['is_read']);
    }

    public function testToArrayWithDefaultTimestamps(): void
    {
        $message = new Message(
            1,
            'John Doe',
            $this->email,
            'Test Subject',
            'Test message content'
        );

        $array = $message->toArray();

        $this->assertIsString($array['created_at']);
        $this->assertIsString($array['updated_at']);
        $this->assertNotEmpty($array['created_at']);
        $this->assertNotEmpty($array['updated_at']);
    }

    public function testMessageWithEmptySubject(): void
    {
        $message = new Message(
            1,
            'John Doe',
            $this->email,
            '',
            'Test message content'
        );

        $this->assertEquals('', $message->getSubject());
        $this->assertEquals('', $message->toArray()['subject']);
    }

    public function testMessageWithLongContent(): void
    {
        $longMessage = str_repeat('This is a test message. ', 1000);

        $message = new Message(
            1,
            'John Doe',
            $this->email,
            'Test Subject',
            $longMessage
        );

        $this->assertEquals($longMessage, $message->getMessage());
        $this->assertEquals($longMessage, $message->toArray()['message']);
    }

    public function testMessageStatusTransitions(): void
    {
        $message = new Message(
            1,
            'John Doe',
            $this->email,
            'Test Subject',
            'Test message content'
        );

        // Start as pending
        $this->assertEquals(MessageStatus::pending(), $message->getStatus());

        // Update to delivered
        $message->updateStatus(MessageStatus::delivered());
        $this->assertEquals(MessageStatus::delivered(), $message->getStatus());

        // Update to bounced
        $message->updateStatus(MessageStatus::bounced());
        $this->assertEquals(MessageStatus::bounced(), $message->getStatus());
    }

    public function testMessageReadStatusTransitions(): void
    {
        $message = new Message(
            1,
            'John Doe',
            $this->email,
            'Test Subject',
            'Test message content'
        );

        // Start as unread
        $this->assertFalse($message->isRead());

        // Mark as read
        $message->markAsRead();
        $this->assertTrue($message->isRead());

        // Should stay read (no method to mark as unread)
        $this->assertTrue($message->isRead());
    }

    public function testMessageTimestampsAreImmutable(): void
    {
        $originalCreatedAt = $this->createdAt;
        $originalUpdatedAt = $this->updatedAt;

        $message = new Message(
            1,
            'John Doe',
            $this->email,
            'Test Subject',
            'Test message content',
            null,
            MessageStatus::pending(),
            false,
            $originalCreatedAt,
            $originalUpdatedAt
        );

        // Original timestamps should not be modified
        $this->assertSame($originalCreatedAt, $message->getCreatedAt());
        $this->assertSame($originalUpdatedAt, $message->getUpdatedAt());

        // Operations should create new timestamps
        $message->markAsRead();
        $this->assertNotSame($originalUpdatedAt, $message->getUpdatedAt());
        $this->assertSame($originalCreatedAt, $message->getCreatedAt()); // CreatedAt should not change
    }
}