<?php

namespace App\Application\Services;

use App\Domain\Interfaces\AdminRepositoryInterface;
use App\Domain\Interfaces\AuthenticationServiceInterface;
use App\Domain\Entities\Admin;

class AdminAuthenticationService implements AuthenticationServiceInterface
{
    private AdminRepositoryInterface $adminRepository;

    public function __construct(AdminRepositoryInterface $adminRepository)
    {
        $this->adminRepository = $adminRepository;
    }

    public function authenticate(string $username, string $password): ?Admin
    {
        $admin = $this->adminRepository->findByUsername($username);

        if (!$admin || !$admin->verifyPassword($password)) {
            return null;
        }

        return $admin;
    }

    public function isAuthenticated(): bool
    {
        return session_status() === PHP_SESSION_ACTIVE &&
               isset($_SESSION['admin_logged_in']) &&
               $_SESSION['admin_logged_in'] === true;
    }

    public function getCurrentAdmin(): ?Admin
    {
        if (!$this->isAuthenticated() || !isset($_SESSION['admin_username'])) {
            return null;
        }

        return $this->adminRepository->findByUsername($_SESSION['admin_username']);
    }

    public function login(Admin $admin): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return false;
        }

        // Regenerate session ID for security
        session_regenerate_id(true);

        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $admin->getUsername();
        $_SESSION['last_regen'] = time();

        return true;
    }

    public function logout(): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return false;
        }

        // Unset all session variables
        $_SESSION = [];

        // Destroy the session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        // Destroy the session
        session_destroy();

        return true;
    }

    public function regenerateSession(): bool
    {
        if (!$this->isAuthenticated()) {
            return false;
        }

        // Only regenerate session if it's been more than 30 minutes since last regeneration
        $lastRegen = $_SESSION['last_regen'] ?? time();
        $regenerationInterval = 30 * 60; // 30 minutes

        if ((time() - $lastRegen) > $regenerationInterval) {
            session_regenerate_id(true);
            $_SESSION['last_regen'] = time();
            return true;
        }

        return false; // No regeneration needed
    }

    public function validateSessionTimeout(): bool
    {
        if (!$this->isAuthenticated()) {
            return false;
        }

        $sessionLifetime = ini_get('session.gc_maxlifetime');
        $lastRegen = $_SESSION['last_regen'] ?? time();

        if ((time() - $lastRegen) > $sessionLifetime) {
            $this->logout();
            return false;
        }

        return true;
    }
}