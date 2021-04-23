<?php

namespace App\Http\Managers;

use App\Models\Status;
use App\Models\Rocket;
use Illuminate\Support\Facades\DB;

class StatusManager
{
    public const TABLE = "launch_status";

    public const SELECT = [
        self::TABLE . '.' . Status::KEY_ID,
        self::TABLE . '.' . Status::KEY_NAME,
        self::TABLE . '.' . Status::KEY_PROBABILITY,
        self::TABLE . '.' . Status::KEY_TBD,
    ];

    /**
     * @param int|string $id
     * @return Status|null
     */
    public function getStatusById($id): ?Status
    {
        $result = DB::table(self::TABLE)
            ->select(self::SELECT)
            ->where(Status::KEY_ID, "=", $id)
            ->first();

        if ($result === null) {
            return null;
        }

        return $this->buildStatusFromDatabaseResult($result);
    }

    /**
     * @param int|string $id
     * @return Status|null
     */
    public function getStatusByLaunchId($id): ?Status
    {
        $result = DB::table(self::TABLE)
            ->select(self::SELECT)
            ->where(Status::KEY_LAUNCH_ID, "=", $id)
            ->first();

        if ($result === null) {
            return null;
        }

        return $this->buildStatusFromDatabaseResult($result);
    }

    /**
     * @param string $name
     * @return Status|null
     */
    public function getStatusByName(string $name): ?Status
    {
        $result = DB::table(self::TABLE)
            ->select(self::SELECT)
            ->where(Status::KEY_NAME, "=", $name)
            ->first();

        if ($result === null) {
            return null;
        }

        return $this->buildStatusFromDatabaseResult($result);
    }

    /**
     * @param string|int $launchId
     * @param string $name
     * @param float $probability
     * @param bool $tbd
     * @return Status
     */
    public function createStatus($launchId, string $name, float $probability, bool $tbd): Status
    {
        $id = DB::table(self::TABLE)
            ->insertGetId([
                Status::KEY_LAUNCH_ID => $launchId,
                Status::KEY_NAME => $name,
                Status::KEY_PROBABILITY => $probability,
                Status::KEY_TBD => $tbd,
            ]);

        return $this->getStatusById($id);
    }

    /**
     * @param string|int $launchId
     * @param string|null $name
     * @param float|null $probability
     * @param bool $tbd
     * @return bool
     */
    public function updateStatus($launchId, ?string $name, ?float $probability, ?bool $tbd): bool
    {
        if ($this->getStatusByLaunchId($launchId) === null) {
            return false;
        }

        DB::table(self::TABLE)
            ->update($this->buildUpdateArray(
                $name, $probability, $tbd
            ));
        return true;
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
     * @return Status
     */
    private function buildStatusFromDatabaseResult($result): Status
    {
        $launchStatus = new Status();

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

    /**
     * @param string|null $name
     * @param float|null $probability
     * @param bool|null $tbd
     * @return array
     */
    private function buildUpdateArray(?string $name, ?float $probability, ?bool $tbd): array
    {
        $array = [];

        if ($name !== null) {
            $array[Status::KEY_NAME] = $name;
        }

        if ($probability !== null) {
            $array[Status::KEY_PROBABILITY] = $probability;
        }

        if ($tbd !== null) {
            $array[Status::KEY_TBD] = $tbd;
        }

        return $array;
    }
}
