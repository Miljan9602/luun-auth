<?php

namespace App\Repository\User;

use App\Models\User;

class EloquentUserRepository implements UserRepositoryInterface
{
    public function getUserById(string $userId): ?User
    {
        return User::where('twitter_id', $userId)->first();
    }

    public function updateOrCreateUser(string $userId, array $data): User
    {
        return User::updateOrCreate(
            ['twitter_id' => $userId],
            $data
        );
    }
}
