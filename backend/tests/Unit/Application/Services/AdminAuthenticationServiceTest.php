<?php

namespace App\Tests\Unit\Application\Services;

use App\Application\Services\AdminAuthenticationService;
use App\Domain\Entities\Admin;
use App\Domain\Interfaces\AdminRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Mockery as m;

class AdminAuthenticationServiceTest extends TestCase
{
    private AdminAuthenticationService $service;
    private AdminRepositoryInterface $adminRepository;
    private Admin $admin;

    protected function setUp(): void
    {
        $this->adminRepository = m::mock(AdminRepositoryInterface::class);
        $this->service = new AdminAuthenticationService($this->adminRepository);

        $this->admin = new Admin(1, 'testadmin', 'hashed_password', new \DateTimeImmutable(), new \DateTimeImmutable());
    }

    protected function tearDown(): void
    {
        m::close();
        // Clean up session if it exists
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    public function testConstructor(): void
    {
        $this->assertInstanceOf(AdminAuthenticationService::class, $this->service);
    }

    public function testAuthenticateWithValidCredentials(): void
    {
        $username = 'testadmin';
        $password = 'correct_password';

        $this->adminRepository->shouldReceive('findByUsername')
            ->once()
            ->with($username)
            ->andReturn($this->admin);

        // Mock the verifyPassword method on the Admin entity
        $adminMock = m::mock(Admin::class)->makePartial();
        $adminMock->shouldReceive('verifyPassword')
            ->once()
            ->with($password)
            ->andReturn(true);

        $this->adminRepository->shouldReceive('findByUsername')
            ->once()
            ->with($username)
            ->andReturn($adminMock);

        $result = $this->service->authenticate($username, $password);

        $this->assertInstanceOf(Admin::class, $result);
    }

    public function testAuthenticateWithInvalidUsername(): void
    {
        $username = 'nonexistent';
        $password = 'password';

        $this->adminRepository->shouldReceive('findByUsername')
            ->once()
            ->with($username)
            ->andReturn(null);

        $result = $this->service->authenticate($username, $password);

        $this->assertNull($result);
    }

    public function testAuthenticateWithInvalidPassword(): void
    {
        $username = 'testadmin';
        $password = 'wrong_password';

        $adminMock = m::mock(Admin::class)->makePartial();
        $adminMock->shouldReceive('verifyPassword')
            ->once()
            ->with($password)
            ->andReturn(false);

        $this->adminRepository->shouldReceive('findByUsername')
            ->once()
            ->with($username)
            ->andReturn($adminMock);

        $result = $this->service->authenticate($username, $password);

        $this->assertNull($result);
    }

    public function testIsAuthenticatedWhenSessionActiveAndLoggedIn(): void
    {
        // Start session
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $_SESSION['admin_logged_in'] = true;

        $result = $this->service->isAuthenticated();

        $this->assertTrue($result);
    }

    public function testIsAuthenticatedWhenSessionNotActive(): void
    {
        // Ensure session is not active
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }

        $result = $this->service->isAuthenticated();

        $this->assertFalse($result);
    }

    public function testIsAuthenticatedWhenNotLoggedIn(): void
    {
        // Start session but don't set logged in flag
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        unset($_SESSION['admin_logged_in']);

        $result = $this->service->isAuthenticated();

        $this->assertFalse($result);
    }

    public function testGetCurrentAdminWhenAuthenticated(): void
    {
        // Start session
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = 'testadmin';

        $this->adminRepository->shouldReceive('findByUsername')
            ->once()
            ->with('testadmin')
            ->andReturn($this->admin);

        $result = $this->service->getCurrentAdmin();

        $this->assertInstanceOf(Admin::class, $result);
        $this->assertEquals('testadmin', $result->getUsername());
    }

    public function testGetCurrentAdminWhenNotAuthenticated(): void
    {
        // Start session but don't set authentication
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        unset($_SESSION['admin_logged_in']);

        $result = $this->service->getCurrentAdmin();

        $this->assertNull($result);
    }

    public function testGetCurrentAdminWhenUsernameNotSet(): void
    {
        // Start session
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $_SESSION['admin_logged_in'] = true;
        unset($_SESSION['admin_username']);

        $result = $this->service->getCurrentAdmin();

        $this->assertNull($result);
    }

    public function testLoginSuccess(): void
    {
        // Start session
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $result = $this->service->login($this->admin);

        $this->assertTrue($result);
        $this->assertEquals(true, $_SESSION['admin_logged_in']);
        $this->assertEquals('testadmin', $_SESSION['admin_username']);
        $this->assertArrayHasKey('last_regen', $_SESSION);
    }

    public function testLoginWhenSessionNotActive(): void
    {
        // Ensure session is not active
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }

        $result = $this->service->login($this->admin);

        $this->assertFalse($result);
    }

    public function testLogoutSuccess(): void
    {
        // Start session and set some data
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = 'testadmin';
        $_SESSION['other_data'] = 'test';

        $result = $this->service->logout();

        $this->assertTrue($result);
        $this->assertEmpty($_SESSION);
        $this->assertEquals(PHP_SESSION_NONE, session_status());
    }

    public function testLogoutWhenSessionNotActive(): void
    {
        // Ensure session is not active
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }

        $result = $this->service->logout();

        $this->assertFalse($result);
    }

    public function testRegenerateSessionWhenAuthenticatedAndExpired(): void
    {
        // Start session
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $_SESSION['admin_logged_in'] = true;
        $_SESSION['last_regen'] = time() - (31 * 60); // 31 minutes ago

        $result = $this->service->regenerateSession();

        $this->assertTrue($result);
        $this->assertArrayHasKey('last_regen', $_SESSION);
    }

    public function testRegenerateSessionWhenAuthenticatedAndNotExpired(): void
    {
        // Start session
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $_SESSION['admin_logged_in'] = true;
        $_SESSION['last_regen'] = time() - (15 * 60); // 15 minutes ago

        $result = $this->service->regenerateSession();

        $this->assertFalse($result);
    }

    public function testRegenerateSessionWhenNotAuthenticated(): void
    {
        // Start session but don't authenticate
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        unset($_SESSION['admin_logged_in']);

        $result = $this->service->regenerateSession();

        $this->assertFalse($result);
    }

    public function testValidateSessionTimeoutWhenAuthenticatedAndNotExpired(): void
    {
        // Start session
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $_SESSION['admin_logged_in'] = true;
        $_SESSION['last_regen'] = time() - (30 * 60); // 30 minutes ago

        $result = $this->service->validateSessionTimeout();

        $this->assertTrue($result);
    }

    public function testValidateSessionTimeoutWhenAuthenticatedAndExpired(): void
    {
        // Start session
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $_SESSION['admin_logged_in'] = true;
        $_SESSION['last_regen'] = time() - (25 * 60 * 60); // 25 hours ago

        $result = $this->service->validateSessionTimeout();

        $this->assertFalse($result);
        $this->assertEmpty($_SESSION);
    }

    public function testValidateSessionTimeoutWhenNotAuthenticated(): void
    {
        // Start session but don't authenticate
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        unset($_SESSION['admin_logged_in']);

        $result = $this->service->validateSessionTimeout();

        $this->assertFalse($result);
    }

    public function testAuthenticateHandlesRepositoryException(): void
    {
        $username = 'testadmin';
        $password = 'password';

        $this->adminRepository->shouldReceive('findByUsername')
            ->once()
            ->with($username)
            ->andThrow(new \Exception('Database connection failed'));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Database connection failed');

        $this->service->authenticate($username, $password);
    }

    public function testGetCurrentAdminHandlesRepositoryException(): void
    {
        // Start session
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = 'testadmin';

        $this->adminRepository->shouldReceive('findByUsername')
            ->once()
            ->with('testadmin')
            ->andThrow(new \Exception('Database connection failed'));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Database connection failed');

        $this->service->getCurrentAdmin();
    }
}