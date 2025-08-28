<?php

namespace App\Domain\Interfaces;

use App\Domain\Entities\Admin;

interface AuthenticationServiceInterface
{
    /**
     * Authenticate admin user
     */
    public function authenticate(string $username, string $password): ?Admin;

    /**
     * Verify if current session is authenticated
     */
    public function isAuthenticated(): bool;

    /**
     * Get current authenticated admin
     */
    public function getCurrentAdmin(): ?Admin;

    /**
     * Login admin (start session)
     */
    public function login(Admin $admin): bool;

    /**
     * Logout current admin (destroy session)
     */
    public function logout(): bool;

    /**
     * Regenerate session ID for security
     */
    public function regenerateSession(): bool;

    /**
     * Validate session timeout
     */
    public function validateSessionTimeout(): bool;
}