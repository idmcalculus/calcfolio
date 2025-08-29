<?php

namespace App\Tests\Unit\Domain\ValueObjects;

use App\Domain\ValueObjects\MessageStatus;
use PHPUnit\Framework\TestCase;

class MessageStatusTest extends TestCase
{
    public function testPendingStatus(): void
    {
        $status = MessageStatus::pending();

        $this->assertEquals('pending', $status->getValue());
        $this->assertEquals('pending', (string) $status);
    }

    public function testDeliveredStatus(): void
    {
        $status = MessageStatus::delivered();

        $this->assertEquals('delivered', $status->getValue());
        $this->assertEquals('delivered', (string) $status);
    }

    public function testBouncedStatus(): void
    {
        $status = MessageStatus::bounced();

        $this->assertEquals('bounced', $status->getValue());
        $this->assertEquals('bounced', (string) $status);
    }

    public function testAllStatuses(): void
    {
        $statuses = [
            MessageStatus::pending(),
            MessageStatus::delivered(),
            MessageStatus::bounced(),
        ];

        $expectedValues = ['pending', 'delivered', 'bounced'];

        foreach ($statuses as $index => $status) {
            $this->assertEquals($expectedValues[$index], $status->getValue());
        }
    }

    public function testStatusEquality(): void
    {
        $pending1 = MessageStatus::pending();
        $pending2 = MessageStatus::pending();

        $this->assertTrue($pending1->equals($pending2));
        $this->assertEquals($pending1, $pending2);
    }

    public function testStatusInequality(): void
    {
        $pending = MessageStatus::pending();
        $delivered = MessageStatus::delivered();
        $bounced = MessageStatus::bounced();

        $this->assertFalse($pending->equals($delivered));
        $this->assertFalse($pending->equals($bounced));
        $this->assertFalse($delivered->equals($bounced));
    }

    public function testToString(): void
    {
        $status = MessageStatus::delivered();

        $this->assertEquals('delivered', (string) $status);
        $this->assertEquals($status->getValue(), (string) $status);
    }

    public function testStatusImmutability(): void
    {
        $status1 = MessageStatus::pending();
        $status2 = MessageStatus::pending();

        // Both should be the same instance (singleton pattern)
        $this->assertSame($status1, $status2);
    }

    public function testStatusConsistency(): void
    {
        // Test that multiple calls return the same instance
        $pending1 = MessageStatus::pending();
        $pending2 = MessageStatus::pending();
        $pending3 = MessageStatus::pending();

        $this->assertSame($pending1, $pending2);
        $this->assertSame($pending2, $pending3);
        $this->assertSame($pending1, $pending3);
    }

    public function testAllStatusInstancesAreUnique(): void
    {
        $pending = MessageStatus::pending();
        $delivered = MessageStatus::delivered();
        $bounced = MessageStatus::bounced();

        $this->assertNotSame($pending, $delivered);
        $this->assertNotSame($pending, $bounced);
        $this->assertNotSame($delivered, $bounced);
    }

    public function testStatusValuesAreCorrect(): void
    {
        $this->assertEquals('pending', MessageStatus::pending()->getValue());
        $this->assertEquals('delivered', MessageStatus::delivered()->getValue());
        $this->assertEquals('bounced', MessageStatus::bounced()->getValue());
    }

    public function testStatusStringRepresentation(): void
    {
        $this->assertEquals('pending', (string) MessageStatus::pending());
        $this->assertEquals('delivered', (string) MessageStatus::delivered());
        $this->assertEquals('bounced', (string) MessageStatus::bounced());
    }

    public function testStatusComparisonWithEqualsMethod(): void
    {
        $pending = MessageStatus::pending();

        $this->assertTrue($pending->equals(MessageStatus::pending()));
        $this->assertFalse($pending->equals(MessageStatus::delivered()));
        $this->assertFalse($pending->equals(MessageStatus::bounced()));
    }

    public function testStatusComparisonWithDelivered(): void
    {
        $delivered = MessageStatus::delivered();

        $this->assertFalse($delivered->equals(MessageStatus::pending()));
        $this->assertTrue($delivered->equals(MessageStatus::delivered()));
        $this->assertFalse($delivered->equals(MessageStatus::bounced()));
    }

    public function testStatusComparisonWithBounced(): void
    {
        $bounced = MessageStatus::bounced();

        $this->assertFalse($bounced->equals(MessageStatus::pending()));
        $this->assertFalse($bounced->equals(MessageStatus::delivered()));
        $this->assertTrue($bounced->equals(MessageStatus::bounced()));
    }

    public function testStatusCanBeUsedInSwitchStatements(): void
    {
        $status = MessageStatus::delivered();
        $result = '';

        switch ($status->getValue()) {
            case 'pending':
                $result = 'Message is pending';
                break;
            case 'delivered':
                $result = 'Message was delivered';
                break;
            case 'bounced':
                $result = 'Message bounced';
                break;
            default:
                $result = 'Unknown status';
        }

        $this->assertEquals('Message was delivered', $result);
    }

    public function testStatusSerialization(): void
    {
        $status = MessageStatus::bounced();

        // Test that we can serialize and get the value back
        $serialized = serialize($status);
        $unserialized = unserialize($serialized);

        $this->assertEquals($status->getValue(), $unserialized->getValue());
        $this->assertTrue($status->equals($unserialized));
    }

    public function testStatusInArrayOperations(): void
    {
        $statuses = [
            MessageStatus::pending(),
            MessageStatus::delivered(),
            MessageStatus::bounced(),
        ];

        // Test array search
        $searchResult = array_search(MessageStatus::delivered(), $statuses);
        $this->assertIsInt($searchResult);
        $this->assertGreaterThanOrEqual(0, $searchResult);

        // Test in_array
        $this->assertTrue(in_array(MessageStatus::pending(), $statuses));
        $this->assertTrue(in_array(MessageStatus::delivered(), $statuses));
        $this->assertTrue(in_array(MessageStatus::bounced(), $statuses));
        $this->assertFalse(in_array('invalid_status', $statuses));
    }

    public function testStatusAsArrayKey(): void
    {
        $statusMap = [
            MessageStatus::pending() => 'Waiting to be sent',
            MessageStatus::delivered() => 'Successfully sent',
            MessageStatus::bounced() => 'Failed to deliver',
        ];

        $this->assertCount(3, $statusMap);
        $this->assertArrayHasKey(MessageStatus::pending(), $statusMap);
        $this->assertArrayHasKey(MessageStatus::delivered(), $statusMap);
        $this->assertArrayHasKey(MessageStatus::bounced(), $statusMap);

        $this->assertEquals('Successfully sent', $statusMap[MessageStatus::delivered()]);
    }
}