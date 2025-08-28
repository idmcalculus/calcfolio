<?php

namespace App\Domain\Interfaces;

use App\Domain\Entities\Message;
use Illuminate\Pagination\LengthAwarePaginator;

interface MessageRepositoryInterface
{
    /**
     * Find a message by ID
     */
    public function findById(int $id): ?Message;

    /**
     * Find a message by message ID (external identifier)
     */
    public function findByMessageId(string $messageId): ?Message;

    /**
     * Get paginated messages with optional filters
     */
    public function getPaginated(
        int $page = 1,
        int $perPage = 15,
        array $filters = [],
        string $sortBy = 'created_at',
        string $sortOrder = 'desc'
    ): LengthAwarePaginator;

    /**
     * Create a new message
     */
    public function create(array $data): Message;

    /**
     * Update an existing message
     */
    public function update(int $id, array $data): bool;

    /**
     * Delete a message
     */
    public function delete(int $id): bool;

    /**
     * Mark message as read
     */
    public function markAsRead(int $id): bool;

    /**
     * Get messages count with optional filters
     */
    public function count(array $filters = []): int;

    /**
     * Search messages
     */
    public function search(string $query, array $filters = []): array;
}