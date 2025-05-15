<?php

namespace App\Repository\User;

use App\Models\User;

interface UserRepositoryInterface
{
    /**
     * @param string $userId
     * @return User|null
     */
    public function getUserById(string $userId): ?User;

    /**
     * @param string $userId
     * @param array $data
     * @return User
     */
    public function updateOrCreateUser(string $userId, array $data): User;
}
