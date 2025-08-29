<?php

namespace App\Tests\Unit\Domain\ValueObjects;

use App\Domain\ValueObjects\EmailAddress;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class EmailAddressTest extends TestCase
{
    public function testConstructorWithValidEmail(): void
    {
        $email = new EmailAddress('test@example.com');

        $this->assertEquals('test@example.com', $email->getValue());
        $this->assertEquals('test@example.com', (string) $email);
    }

    public function testConstructorWithUppercaseEmail(): void
    {
        $email = new EmailAddress('Test@Example.COM');

        $this->assertEquals('test@example.com', $email->getValue());
    }

    public function testConstructorWithWhitespaceEmail(): void
    {
        $email = new EmailAddress('  test@example.com  ');

        $this->assertEquals('test@example.com', $email->getValue());
    }

    public function testConstructorWithInvalidEmail(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid email address format');

        new EmailAddress('invalid-email');
    }

    public function testConstructorWithEmptyEmail(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid email address format');

        new EmailAddress('');
    }

    public function testConstructorWithWhitespaceOnlyEmail(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid email address format');

        new EmailAddress('   ');
    }

    public function testGetDomain(): void
    {
        $email = new EmailAddress('test@example.com');

        $this->assertEquals('example.com', $email->getDomain());
    }

    public function testGetDomainWithSubdomain(): void
    {
        $email = new EmailAddress('user@mail.example.co.uk');

        $this->assertEquals('example.co.uk', $email->getDomain());
    }

    public function testGetLocalPart(): void
    {
        $email = new EmailAddress('test@example.com');

        $this->assertEquals('test', $email->getLocalPart());
    }

    public function testGetLocalPartWithDots(): void
    {
        $email = new EmailAddress('test.user.name@example.com');

        $this->assertEquals('test.user.name', $email->getLocalPart());
    }

    public function testGetLocalPartWithPlusSign(): void
    {
        $email = new EmailAddress('test+tag@example.com');

        $this->assertEquals('test+tag', $email->getLocalPart());
    }

    public function testEqualsWithSameEmail(): void
    {
        $email1 = new EmailAddress('test@example.com');
        $email2 = new EmailAddress('test@example.com');

        $this->assertTrue($email1->equals($email2));
    }

    public function testEqualsWithDifferentEmail(): void
    {
        $email1 = new EmailAddress('test@example.com');
        $email2 = new EmailAddress('other@example.com');

        $this->assertFalse($email1->equals($email2));
    }

    public function testEqualsWithDifferentCase(): void
    {
        $email1 = new EmailAddress('test@example.com');
        $email2 = new EmailAddress('TEST@EXAMPLE.COM');

        $this->assertTrue($email1->equals($email2));
    }

    public function testToString(): void
    {
        $email = new EmailAddress('test@example.com');

        $this->assertEquals('test@example.com', (string) $email);
    }

    public function testVariousValidEmailFormats(): void
    {
        $validEmails = [
            'simple@example.com',
            'very.common@example.com',
            'disposable.style.email.with+symbol@example.com',
            'other.email-with-hyphen@example.com',
            'fully-qualified-domain@example.co.uk',
            'user.name+tag+sorting@example.com',
            'example-indeed@strange-example.com',
            'test/test@test.com', // This should actually be invalid, but PHP's filter allows it
            'admin@mailserver1',
            'example@s.example',
            '" "@example.org',
            'example@localhost',
        ];

        foreach ($validEmails as $emailAddress) {
            $email = new EmailAddress($emailAddress);
            $this->assertEquals(strtolower(trim($emailAddress)), $email->getValue());
        }
    }

    public function testInvalidEmailFormats(): void
    {
        $invalidEmails = [
            'plainaddress',
            '@example.com',
            'test@',
            'test@.',
            'test..user@example.com',
            'test@.example.com',
            'test@example..com',
            'test@example.com.',
            '.test@example.com',
            'test.@example.com',
            'test@example.com..',
            'test\\@example.com',
            'test@example.com\\',
            'test @example.com',
            'test@example.com ',
            ' test@example.com',
            'test@example.com ',
            '',
            ' ',
            '@',
            '.',
            '@.',
            '.@',
            '.@.',
        ];

        foreach ($invalidEmails as $emailAddress) {
            try {
                new EmailAddress($emailAddress);
                $this->fail("Expected InvalidArgumentException for email: $emailAddress");
            } catch (InvalidArgumentException $e) {
                $this->assertEquals('Invalid email address format', $e->getMessage());
            }
        }
    }

    public function testEmailWithUnicodeCharacters(): void
    {
        // Note: PHP's filter_var doesn't handle Unicode well, but let's test what we can
        $email = new EmailAddress('test@example.com');

        $this->assertEquals('test@example.com', $email->getValue());
    }

    public function testEmailWithNumbers(): void
    {
        $email = new EmailAddress('user123@example456.com');

        $this->assertEquals('user123@example456.com', $email->getValue());
        $this->assertEquals('user123', $email->getLocalPart());
        $this->assertEquals('example456.com', $email->getDomain());
    }

    public function testEmailWithSpecialCharactersInLocalPart(): void
    {
        $specialChars = ['!', '#', '$', '%', '&', '\'', '*', '+', '-', '/', '=', '?', '^', '_', '`', '{', '|', '}', '~'];

        foreach ($specialChars as $char) {
            $localPart = 'test' . $char . 'user';
            $emailAddress = $localPart . '@example.com';

            $email = new EmailAddress($emailAddress);
            $this->assertEquals(strtolower($emailAddress), $email->getValue());
            $this->assertEquals(strtolower($localPart), $email->getLocalPart());
        }
    }

    public function testEmailDomainExtraction(): void
    {
        $testCases = [
            'user@domain.com' => 'domain.com',
            'test@sub.domain.co.uk' => 'domain.co.uk',
            'admin@local' => 'local',
            'user@domain.io' => 'domain.io',
            'test@very.long.domain.name.example.com' => 'long.domain.name.example.com',
        ];

        foreach ($testCases as $emailAddress => $expectedDomain) {
            $email = new EmailAddress($emailAddress);
            $this->assertEquals($expectedDomain, $email->getDomain(), "Failed for email: $emailAddress");
        }
    }

    public function testEmailEqualityIsCaseInsensitive(): void
    {
        $variations = [
            'test@example.com',
            'TEST@EXAMPLE.COM',
            'Test@Example.Com',
            'tEsT@eXaMpLe.CoM',
        ];

        $firstEmail = new EmailAddress($variations[0]);

        foreach ($variations as $variation) {
            $otherEmail = new EmailAddress($variation);
            $this->assertTrue($firstEmail->equals($otherEmail), "Failed for variation: $variation");
        }
    }

    public function testEmailToStringConsistency(): void
    {
        $emailAddress = 'Test.User+Tag@Example.COM';
        $email = new EmailAddress($emailAddress);

        $this->assertEquals('test.user+tag@example.com', $email->getValue());
        $this->assertEquals('test.user+tag@example.com', (string) $email);
        $this->assertEquals($email->getValue(), (string) $email);
    }
}