<?php

namespace App\Tests\Unit\Application\Validators;

use App\Application\Validators\RequestValidator;
use PHPUnit\Framework\TestCase;

class RequestValidatorTest extends TestCase
{
    private RequestValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new RequestValidator();
    }

    public function testValidateContactFormSuccess(): void
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'subject' => 'Test Subject',
            'message' => 'This is a test message with enough content to pass validation.'
        ];

        $result = $this->validator->validateContactForm($data);

        $this->assertTrue($this->validator->isValid());
        $this->assertEquals($data, $result);
        $this->assertEmpty($this->validator->getErrors());
    }

    public function testValidateContactFormWithRecaptchaToken(): void
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'subject' => 'Test Subject',
            'message' => 'This is a test message with enough content to pass validation.',
            'recaptcha_token' => 'recaptcha_token_123'
        ];

        $result = $this->validator->validateContactForm($data);

        $this->assertTrue($this->validator->isValid());
        $this->assertEquals($data, $result);
    }

    public function testValidateContactFormFailsWithEmptyName(): void
    {
        $data = [
            'name' => '',
            'email' => 'john@example.com',
            'subject' => 'Test Subject',
            'message' => 'This is a test message.'
        ];

        $result = $this->validator->validateContactForm($data);

        $this->assertFalse($this->validator->isValid());
        $this->assertEmpty($result);
        $this->assertArrayHasKey('name', $this->validator->getErrors());
    }

    public function testValidateContactFormFailsWithInvalidEmail(): void
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'invalid-email',
            'subject' => 'Test Subject',
            'message' => 'This is a test message.'
        ];

        $result = $this->validator->validateContactForm($data);

        $this->assertFalse($this->validator->isValid());
        $this->assertEmpty($result);
        $this->assertArrayHasKey('email', $this->validator->getErrors());
    }

    public function testValidateContactFormFailsWithEmptySubject(): void
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'subject' => '',
            'message' => 'This is a test message.'
        ];

        $result = $this->validator->validateContactForm($data);

        $this->assertFalse($this->validator->isValid());
        $this->assertEmpty($result);
        $this->assertArrayHasKey('subject', $this->validator->getErrors());
    }

    public function testValidateContactFormFailsWithEmptyMessage(): void
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'subject' => 'Test Subject',
            'message' => ''
        ];

        $result = $this->validator->validateContactForm($data);

        $this->assertFalse($this->validator->isValid());
        $this->assertEmpty($result);
        $this->assertArrayHasKey('message', $this->validator->getErrors());
    }

    public function testValidateContactFormFailsWithTooLongName(): void
    {
        $data = [
            'name' => str_repeat('a', 101), // 101 characters
            'email' => 'john@example.com',
            'subject' => 'Test Subject',
            'message' => 'This is a test message.'
        ];

        $result = $this->validator->validateContactForm($data);

        $this->assertFalse($this->validator->isValid());
        $this->assertEmpty($result);
        $this->assertArrayHasKey('name', $this->validator->getErrors());
    }

    public function testValidateContactFormFailsWithTooLongSubject(): void
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'subject' => str_repeat('a', 201), // 201 characters
            'message' => 'This is a test message.'
        ];

        $result = $this->validator->validateContactForm($data);

        $this->assertFalse($this->validator->isValid());
        $this->assertEmpty($result);
        $this->assertArrayHasKey('subject', $this->validator->getErrors());
    }

    public function testValidateContactFormFailsWithTooLongMessage(): void
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'subject' => 'Test Subject',
            'message' => str_repeat('a', 5001) // 5001 characters
        ];

        $result = $this->validator->validateContactForm($data);

        $this->assertFalse($this->validator->isValid());
        $this->assertEmpty($result);
        $this->assertArrayHasKey('message', $this->validator->getErrors());
    }

    public function testValidateAdminLoginSuccess(): void
    {
        $data = [
            'username' => 'admin',
            'password' => 'password123'
        ];

        $result = $this->validator->validateAdminLogin($data);

        $this->assertTrue($this->validator->isValid());
        $this->assertEquals($data, $result);
    }

    public function testValidateAdminLoginFailsWithEmptyUsername(): void
    {
        $data = [
            'username' => '',
            'password' => 'password123'
        ];

        $result = $this->validator->validateAdminLogin($data);

        $this->assertFalse($this->validator->isValid());
        $this->assertEmpty($result);
        $this->assertArrayHasKey('username', $this->validator->getErrors());
    }

    public function testValidateAdminLoginFailsWithEmptyPassword(): void
    {
        $data = [
            'username' => 'admin',
            'password' => ''
        ];

        $result = $this->validator->validateAdminLogin($data);

        $this->assertFalse($this->validator->isValid());
        $this->assertEmpty($result);
        $this->assertArrayHasKey('password', $this->validator->getErrors());
    }

    public function testValidateBulkActionSuccess(): void
    {
        $data = [
            'action' => 'mark_read',
            'ids' => [1, 2, 3]
        ];

        $result = $this->validator->validateBulkAction($data);

        $this->assertTrue($this->validator->isValid());
        $this->assertEquals($data, $result);
    }

    public function testValidateBulkActionFailsWithInvalidAction(): void
    {
        $data = [
            'action' => 'invalid_action',
            'ids' => [1, 2, 3]
        ];

        $result = $this->validator->validateBulkAction($data);

        $this->assertFalse($this->validator->isValid());
        $this->assertEmpty($result);
        $this->assertArrayHasKey('action', $this->validator->getErrors());
    }

    public function testValidateBulkActionFailsWithEmptyIds(): void
    {
        $data = [
            'action' => 'mark_read',
            'ids' => []
        ];

        $result = $this->validator->validateBulkAction($data);

        $this->assertFalse($this->validator->isValid());
        $this->assertEmpty($result);
        $this->assertArrayHasKey('ids', $this->validator->getErrors());
    }

    public function testValidateBulkActionFailsWithNonPositiveIds(): void
    {
        $data = [
            'action' => 'mark_read',
            'ids' => [1, 0, -1]
        ];

        $result = $this->validator->validateBulkAction($data);

        $this->assertFalse($this->validator->isValid());
        $this->assertEmpty($result);
        $this->assertArrayHasKey('ids', $this->validator->getErrors());
    }

    public function testValidatePaginationSuccess(): void
    {
        $params = [
            'page' => '2',
            'limit' => '10',
            'sort' => 'created_at',
            'order' => 'desc'
        ];

        $result = $this->validator->validatePagination($params);

        $this->assertTrue($this->validator->isValid());
        $this->assertEquals([
            'page' => 2,
            'limit' => 10,
            'sort' => 'created_at',
            'order' => 'desc',
            'is_read' => null,
            'search' => null,
            'status' => null,
        ], $result);
    }

    public function testValidatePaginationWithDefaults(): void
    {
        $params = [];

        $result = $this->validator->validatePagination($params);

        $this->assertTrue($this->validator->isValid());
        $this->assertEquals([
            'page' => 1,
            'limit' => 15,
            'sort' => 'created_at',
            'order' => 'desc',
            'is_read' => null,
            'search' => null,
            'status' => null,
        ], $result);
    }

    public function testValidatePaginationFailsWithInvalidPage(): void
    {
        $params = ['page' => '0'];

        $result = $this->validator->validatePagination($params);

        $this->assertFalse($this->validator->isValid());
        $this->assertEmpty($result);
        $this->assertArrayHasKey('page', $this->validator->getErrors());
    }

    public function testValidatePaginationFailsWithTooLargeLimit(): void
    {
        $params = ['limit' => '10001'];

        $result = $this->validator->validatePagination($params);

        $this->assertFalse($this->validator->isValid());
        $this->assertEmpty($result);
        $this->assertArrayHasKey('limit', $this->validator->getErrors());
    }

    public function testValidatePaginationFailsWithInvalidSort(): void
    {
        $params = ['sort' => 'invalid_sort'];

        $result = $this->validator->validatePagination($params);

        $this->assertFalse($this->validator->isValid());
        $this->assertEmpty($result);
        $this->assertArrayHasKey('sort', $this->validator->getErrors());
    }

    public function testValidatePaginationFailsWithInvalidOrder(): void
    {
        $params = ['order' => 'invalid_order'];

        $result = $this->validator->validatePagination($params);

        $this->assertFalse($this->validator->isValid());
        $this->assertEmpty($result);
        $this->assertArrayHasKey('order', $this->validator->getErrors());
    }

    public function testEmailIsNormalizedToLowercase(): void
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'John.Doe@Example.COM',
            'subject' => 'Test Subject',
            'message' => 'This is a test message.'
        ];

        $result = $this->validator->validateContactForm($data);

        $this->assertTrue($this->validator->isValid());
        $this->assertEquals('john.doe@example.com', $result['email']);
    }

    public function testUsernameIsTrimmed(): void
    {
        $data = [
            'username' => '  admin  ',
            'password' => 'password123'
        ];

        $result = $this->validator->validateAdminLogin($data);

        $this->assertTrue($this->validator->isValid());
        $this->assertEquals('admin', $result['username']);
    }
}