<?php

namespace App\Repository\XUser;

interface XUserRepositoryInterface
{
    /**
     * @param string $userId
     * @return array|null
     */
    public function getUserById(string $userId) : ?array;

    /**
     * @param string $userId
     * @param array $userData
     * @return mixed
     */
    public function createOrUpdateUser(string $userId, array $userData = []);
}
