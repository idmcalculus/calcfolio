<?php

namespace App\Infrastructure\Database;

use App\Domain\Interfaces\AdminRepositoryInterface;
use App\Domain\Entities\Admin;
use DateTimeImmutable;

class EloquentAdminRepository implements AdminRepositoryInterface
{
    public function findById(int $id): ?Admin
    {
        $model = \App\Models\Admin::find($id);

        if (!$model) {
            return null;
        }

        return $this->modelToEntity($model);
    }

    public function findByUsername(string $username): ?Admin
    {
        $model = \App\Models\Admin::where('username', $username)->first();

        if (!$model) {
            return null;
        }

        return $this->modelToEntity($model);
    }

    public function create(array $data): Admin
    {
        $model = \App\Models\Admin::create([
            'username' => $data['username'],
            'password_hash' => $data['password_hash'],
        ]);

        return $this->modelToEntity($model);
    }

    public function update(int $id, array $data): bool
    {
        $model = \App\Models\Admin::find($id);

        if (!$model) {
            return false;
        }

        return $model->update($data);
    }

    public function delete(int $id): bool
    {
        $model = \App\Models\Admin::find($id);

        if (!$model) {
            return false;
        }

        return $model->delete();
    }

    public function usernameExists(string $username, ?int $excludeId = null): bool
    {
        $query = \App\Models\Admin::where('username', $username);

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    public function getAll(): array
    {
        $models = \App\Models\Admin::all();

        return $models->map(function ($model) {
            return $this->modelToEntity($model);
        })->toArray();
    }

    private function modelToEntity(\App\Models\Admin $model): Admin
    {
        return new Admin(
            $model->id,
            $model->username,
            $model->password_hash,
            new DateTimeImmutable($model->created_at),
            new DateTimeImmutable($model->updated_at)
        );
    }
}