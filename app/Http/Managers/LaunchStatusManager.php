<?php

declare(strict_types=1);

namespace App\Http\Managers;

use App\Models\Launch;
use App\Models\LaunchStatus;
use App\Models\Location;
use Illuminate\Support\Facades\DB;

class LaunchStatusManager
{

    public const TABLE = 'launch_status';
    public const SELECT = [
        LaunchStatus::KEY_ID,
        LaunchStatus::KEY_NAME,
        LaunchStatus::KEY_PROBABILITY,
        LaunchStatus::KEY_TBD,
    ];

    /**
     * @param int|string $id
     * @return Location|null
     */
    public function getLaunchStatusById($id): ?LaunchStatus
    {
        $result = DB::table(self::TABLE)
            ->select(self::SELECT)
            ->where(LaunchStatus::KEY_ID, '=', $id)
            ->first();

        if ($result === null) {
            return null;
        }

        return $this->buildLaunchStatusFromDatabaseResult($result);
    }

    /**
     * @param int|string $launchId
     * @return Location|null
     */
    public function getLaunchStatusByLaunchId($launchId): ?LaunchStatus
    {
        $result = DB::table(self::TABLE)
            ->select(self::SELECT)
            ->where(LaunchStatus::KEY_LAUNCH_ID, '=', $launchId)
            ->first();

        if ($result === null) {
            return null;
        }

        return $this->buildLaunchStatusFromDatabaseResult($result);
    }

    /**
     * @param int|string $launchId
     * @param string|null $name
     * @param float|null $probability
     * @param bool|null $tbd
     * @return Location|null
     */
    public function createLaunchStatus($launchId, string $name, ?float $probability, ?bool $tbd): ?LaunchStatus
    {
        DB::table(self::TABLE)
            ->insert([
                LaunchStatus::KEY_LAUNCH_ID => $launchId,
                LaunchStatus::KEY_NAME => $name,
                LaunchStatus::KEY_PROBABILITY => $probability,
                LaunchStatus::KEY_TBD => $tbd,
            ]);

        return $this->getLaunchStatusByLaunchId($launchId);
    }

    /**
     * @param $result
     * @return LaunchStatus
     */
    private function buildLaunchStatusFromDatabaseResult($result): LaunchStatus
    {
        $launchStatus = new LaunchStatus();

        if (isset($result->id)) {
            $launchStatus->setId($result->id);
        }

        if (isset($result->name)) {
            $launchStatus->setName($result->name);
        }

        if (isset($result->probability)) {
            $launchStatus->setProbability($result->probability);
        }

        if (isset($result->tbd)) {
            $launchStatus->setTBD($result->tbd);
        }

        return $launchStatus;
    }
}
