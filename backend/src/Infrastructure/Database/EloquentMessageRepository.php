<?php

namespace App\Infrastructure\Database;

use App\Domain\Interfaces\MessageRepositoryInterface;
use App\Domain\Entities\Message;
use App\Domain\ValueObjects\EmailAddress;
use App\Domain\ValueObjects\MessageStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use DateTimeImmutable;

class EloquentMessageRepository implements MessageRepositoryInterface
{
    public function findById(int $id): ?Message
    {
        $model = \App\Models\Message::find($id);

        if (!$model) {
            return null;
        }

        return $this->modelToEntity($model);
    }

    public function findByMessageId(string $messageId): ?Message
    {
        $model = \App\Models\Message::where('message_id', $messageId)->first();

        if (!$model) {
            return null;
        }

        return $this->modelToEntity($model);
    }

    public function getPaginated(
        int $page = 1,
        int $perPage = 15,
        array $filters = [],
        string $sortBy = 'created_at',
        string $sortOrder = 'desc'
    ): LengthAwarePaginator {
        $query = \App\Models\Message::query();

        // Apply filters
        if (isset($filters['is_read'])) {
            $query->where('is_read', (bool) $filters['is_read']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Apply search
        if (isset($filters['search']) && !empty($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'LIKE', $searchTerm)
                  ->orWhere('email', 'LIKE', $searchTerm)
                  ->orWhere('subject', 'LIKE', $searchTerm)
                  ->orWhere('message', 'LIKE', $searchTerm);
            });
        }

        // Apply sorting
        $query->orderBy($sortBy, $sortOrder);

        // Get paginated results
        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        // Convert models to Domain entities
        $entities = $paginator->getCollection()->map(function ($model) {
            return $this->modelToEntity($model);
        });

        // Create new paginator with Domain entities
        return new LengthAwarePaginator(
            $entities,
            $paginator->total(),
            $paginator->perPage(),
            $paginator->currentPage(),
            [
                'path' => $paginator->path(),
                'pageName' => $paginator->getPageName(),
            ]
        );
    }

    public function create(array $data): Message
    {
        $model = \App\Models\Message::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'subject' => $data['subject'],
            'message' => $data['message'],
            'message_id' => $data['message_id'] ?? null,
            'status' => $data['status'] ?? MessageStatus::STATUS_PENDING,
            'is_read' => $data['is_read'] ?? false,
        ]);

        return $this->modelToEntity($model);
    }

    public function update(int $id, array $data): bool
    {
        $model = \App\Models\Message::find($id);

        if (!$model) {
            return false;
        }

        return $model->update($data);
    }

    public function delete(int $id): bool
    {
        $model = \App\Models\Message::find($id);

        if (!$model) {
            return false;
        }

        return $model->delete();
    }

    public function markAsRead(int $id): bool
    {
        return $this->update($id, ['is_read' => true]);
    }

    public function count(array $filters = []): int
    {
        $query = \App\Models\Message::query();

        if (isset($filters['is_read'])) {
            $query->where('is_read', (bool) $filters['is_read']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->count();
    }

    public function search(string $query, array $filters = []): array
    {
        $searchTerm = '%' . $query . '%';
        $eloquentQuery = \App\Models\Message::where(function ($q) use ($searchTerm) {
            $q->where('name', 'LIKE', $searchTerm)
              ->orWhere('email', 'LIKE', $searchTerm)
              ->orWhere('subject', 'LIKE', $searchTerm)
              ->orWhere('message', 'LIKE', $searchTerm);
        });

        // Apply additional filters
        if (isset($filters['is_read'])) {
            $eloquentQuery->where('is_read', (bool) $filters['is_read']);
        }

        if (isset($filters['status'])) {
            $eloquentQuery->where('status', $filters['status']);
        }

        $models = $eloquentQuery->get();

        return $models->map(function ($model) {
            return $this->modelToEntity($model);
        })->toArray();
    }

    private function modelToEntity(\App\Models\Message $model): Message
    {
        return new Message(
            $model->id,
            $model->name,
            new EmailAddress($model->email),
            $model->subject,
            $model->message,
            $model->message_id,
            MessageStatus::fromString($model->status),
            (bool) $model->is_read,
            new DateTimeImmutable($model->created_at),
            new DateTimeImmutable($model->updated_at)
        );
    }
}