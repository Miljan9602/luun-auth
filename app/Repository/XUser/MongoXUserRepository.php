<?php

namespace App\Repository\XUser;

use Illuminate\Support\Facades\DB;

class MongoXUserRepository implements XUserRepositoryInterface
{
    public function getUserById(string $userId): ?array
    {
        $result = DB::connection('mongodb')->table('users')->where(['user_id' => $userId])->first();

        return $result ? (array) $result : null;
    }

    public function createOrUpdateUser(string $userId, array $userData = [])
    {
        return DB::connection('mongodb')->table('users')->updateOrInsert(['user_id' => $userId], $userData);
    }

}
