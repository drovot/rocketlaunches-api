<?php

namespace App\Http\Managers;

use App\Models\LaunchStatus;
use App\Models\Rocket;
use Illuminate\Support\Facades\DB;

class StatusManager
{
    private const TABLE = "rl_status";

    private const SELECT = [
        "id", "displayName", "cancelled"
    ];

    /**
     * @param int|string $id
     * @return Rocket|null
     */
    public function getStatusById($id): ?LaunchStatus
    {
        $result = DB::table(self::TABLE)
            ->select(self::SELECT)
            ->where("id", "=", $id)
            ->first();

        if ($result === null) {
            return null;
        }

        return $this->buildStatusFromDatabaseResult($result);
    }

    /**
     * @param string $displayName
     * @return Rocket|null
     */
    public function getStatusByDisplayName(string $displayName): ?LaunchStatus
    {
        $result = DB::table(self::TABLE)
            ->select(self::SELECT)
            ->where("displayName", "=", $displayName)
            ->first();

        if ($result === null) {
            return null;
        }

        return $this->buildStatusFromDatabaseResult($result);
    }

    /**
     * @return int|string
     */
    public function getTotalAmount()
    {
        return DB::table(self::TABLE)->selectRaw("COUNT(*) as total")->first()->total ?? 0;
    }

    /**
     * @param $result
     * @return LaunchStatus
     */
    private function buildStatusFromDatabaseResult($result): LaunchStatus
    {
        $launchStatus = new LaunchStatus();

        if (isset($result->id)) {
            $launchStatus->setId($result->id);
        }

        if (isset($result->displayName)) {
            $launchStatus->setDisplayName($result->displayName);
        }

        if (isset($result->cancelled)) {
            $launchStatus->setCancelled($result->cancelled);
        }

        return $launchStatus;
    }
}
