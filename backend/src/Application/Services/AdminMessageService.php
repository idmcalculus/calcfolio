<?php

namespace App\Application\Services;

use App\Domain\Interfaces\MessageRepositoryInterface;
use App\Domain\Interfaces\AuthenticationServiceInterface;
use App\Domain\Interfaces\ValidationInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class AdminMessageService
{
    private MessageRepositoryInterface $messageRepository;
    private AuthenticationServiceInterface $authService;
    private ValidationInterface $validator;

    public function __construct(
        MessageRepositoryInterface $messageRepository,
        AuthenticationServiceInterface $authService,
        ValidationInterface $validator
    ) {
        $this->messageRepository = $messageRepository;
        $this->authService = $authService;
        $this->validator = $validator;
    }

    /**
     * Get paginated messages for admin
     */
    public function getMessages(array $params = []): array
    {
        if (!$this->authService->isAuthenticated()) {
            return [
                'success' => false,
                'message' => 'Unauthorized'
            ];
        }

        // Validate pagination parameters
        $paginationValidation = $this->validator->validatePagination($params);
        if (!$this->validator->isValid()) {
            return [
                'success' => false,
                'message' => 'Invalid pagination parameters',
                'errors' => $this->validator->getErrors()
            ];
        }

        $page = $params['page'] ?? 1;
        $perPage = $params['limit'] ?? 15;
        $sortBy = $params['sort'] ?? 'created_at';
        $sortOrder = $params['order'] ?? 'desc';

        // Build filters
        $filters = [];
        if (isset($params['is_read'])) {
            $filters['is_read'] = (bool) $params['is_read'];
        }
        if (isset($params['status'])) {
            $filters['status'] = $params['status'];
        }
        if (isset($params['search'])) {
            $filters['search'] = $params['search'];
        }

        try {
            // Check for potential memory issues with large datasets
            if ($perPage > 5000) {
                // Log large query for monitoring
                error_log("Large dataset query: {$perPage} records requested");
            }

            $paginator = $this->messageRepository->getPaginated(
                $page,
                $perPage,
                $filters,
                $sortBy,
                $sortOrder
            );

            // Convert Domain entities to arrays for API response
            $items = array_map(function ($entity) {
                return $entity->toArray();
            }, $paginator->items());

            return [
                'success' => true,
                'data' => $items,
                'pagination' => [
                    'total' => $paginator->total(),
                    'per_page' => $paginator->perPage(),
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'from' => $paginator->firstItem(),
                    'to' => $paginator->lastItem(),
                ]
            ];
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            error_log('Error fetching admin messages: ' . $errorMessage);

            // Check for memory-related errors
            if (strpos($errorMessage, 'memory') !== false ||
                strpos($errorMessage, 'timeout') !== false ||
                strpos($errorMessage, 'exhausted') !== false) {

                return [
                    'success' => false,
                    'message' => 'Dataset too large. Please reduce the limit or use pagination.',
                    'error_type' => 'resource_exhausted'
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to retrieve messages'
            ];
        }
    }

    /**
     * Get a single message by ID
     */
    public function getMessage(int $id): array
    {
        if (!$this->authService->isAuthenticated()) {
            return [
                'success' => false,
                'message' => 'Unauthorized'
            ];
        }

        try {
            $message = $this->messageRepository->findById($id);

            if (!$message) {
                return [
                    'success' => false,
                    'message' => 'Message not found'
                ];
            }

            // Mark as read if not already
            if (!$message->isRead()) {
                $this->messageRepository->markAsRead($id);
                $message = $this->messageRepository->findById($id); // Refresh
            }

            return [
                'success' => true,
                'data' => $message->toArray()
            ];
        } catch (\Exception $e) {
            error_log('Error fetching message: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to retrieve message'
            ];
        }
    }

    /**
     * Perform bulk actions on messages
     */
    public function bulkAction(string $action, array $ids): array
    {
        if (!$this->authService->isAuthenticated()) {
            return [
                'success' => false,
                'message' => 'Unauthorized'
            ];
        }

        // Validate bulk action data
        $validationResult = $this->validator->validateBulkAction([
            'action' => $action,
            'ids' => $ids
        ]);

        if (!$this->validator->isValid()) {
            return [
                'success' => false,
                'message' => 'Invalid bulk action data',
                'errors' => $this->validator->getErrors()
            ];
        }

        try {
            $affectedRows = 0;

            switch ($action) {
                case 'mark_read':
                    foreach ($ids as $id) {
                        if ($this->messageRepository->markAsRead((int) $id)) {
                            $affectedRows++;
                        }
                    }
                    break;

                case 'mark_unread':
                    foreach ($ids as $id) {
                        if ($this->messageRepository->update((int) $id, ['is_read' => false])) {
                            $affectedRows++;
                        }
                    }
                    break;

                case 'delete':
                    foreach ($ids as $id) {
                        if ($this->messageRepository->delete((int) $id)) {
                            $affectedRows++;
                        }
                    }
                    break;
            }

            return [
                'success' => true,
                'message' => "Bulk action '{$action}' completed",
                'affected_rows' => $affectedRows
            ];
        } catch (\Exception $e) {
            error_log('Error performing bulk action: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to perform bulk action'
            ];
        }
    }

    /**
     * Get message statistics
     */
    public function getStatistics(): array
    {
        if (!$this->authService->isAuthenticated()) {
            return [
                'success' => false,
                'message' => 'Unauthorized'
            ];
        }

        try {
            $total = $this->messageRepository->count();
            $unread = $this->messageRepository->count(['is_read' => false]);
            $read = $total - $unread;

            return [
                'success' => true,
                'statistics' => [
                    'total' => $total,
                    'read' => $read,
                    'unread' => $unread
                ]
            ];
        } catch (\Exception $e) {
            error_log('Error getting statistics: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to get statistics'
            ];
        }
    }
}