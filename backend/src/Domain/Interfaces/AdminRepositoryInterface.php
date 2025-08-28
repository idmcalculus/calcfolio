<?php

namespace App\Domain\Interfaces;

use App\Domain\Entities\Admin;

interface AdminRepositoryInterface
{
    /**
     * Find an admin by ID
     */
    public function findById(int $id): ?Admin;

    /**
     * Find an admin by username
     */
    public function findByUsername(string $username): ?Admin;

    /**
     * Create a new admin
     */
    public function create(array $data): Admin;

    /**
     * Update an existing admin
     */
    public function update(int $id, array $data): bool;

    /**
     * Delete an admin
     */
    public function delete(int $id): bool;

    /**
     * Check if username exists
     */
    public function usernameExists(string $username, ?int $excludeId = null): bool;

    /**
     * Get all admins
     */
    public function getAll(): array;
}