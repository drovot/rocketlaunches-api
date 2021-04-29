<?php

declare(strict_types=1);

namespace App\Http\Managers;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserManager
{

    public const TABLE = 'user';

    public const SELECT = [
        User::KEY_ID,
        User::KEY_API_TOKEN,
        User::KEY_FIRST_NAME,
        User::KEY_LAST_NAME,
        User::KEY_EMAIL
    ];

    /**
     * @param string $apiToken
     * @return User|null
     */
    public function getUserByApiToken(string $apiToken): ?User
    {
        $result = DB::table(self::TABLE)
            ->select(self::SELECT)
            ->where(User::KEY_API_TOKEN, '=', $apiToken)
            ->first();

        if ($result === null) {
            return null;
        }

        return $this->buildUserFromDatabaseResult($result);
    }

    /**
     * @param string|int $id
     * @return User|null
     */
    public function getUserById($id): ?User
    {
        $result = DB::table(self::TABLE)
            ->select(self::SELECT)
            ->where(User::KEY_ID, '=', $id)
            ->first();

        if ($result === null) {
            return null;
        }

        return $this->buildUserFromDatabaseResult($result);
    }

    /**
     * @param $result
     * @return User
     */
    private function buildUserFromDatabaseResult($result): User
    {
        $rocket = new User();

        if (isset($result->id)) {
            $rocket->setId($result->id);
        }

        if (isset($result->first_name)) {
            $rocket->setFirstname($result->first_name);
        }

        if (isset($result->last_name)) {
            $rocket->setLastname($result->last_name);
        }

        if (isset($result->email)) {
            $rocket->setEmail($result->email);
        }

        return $rocket;
    }
}
