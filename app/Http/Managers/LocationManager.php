<?php

declare(strict_types=1);

namespace App\Http\Managers;

use App\Models\Launch;
use App\Models\Location;
use Illuminate\Support\Facades\DB;

class LocationManager
{

    public const TABLE = 'rl_location';

    public const SELECT = [
        Location::KEY_ID,
        Location::KEY_NAME,
        Location::KEY_COUNTRY_CODE,
        Location::KEY_LATITUDE,
        Location::KEY_LONGITUDE
    ];

    /**
     * @param int|string $id
     * @return Location|null
     */
    public function getLocationById($id): ?Location
    {
        $result = DB::table(self::TABLE)
            ->select(self::SELECT)
            ->where(Launch::KEY_ID, '=', $id)
            ->first();

        if ($result === null) {
            return null;
        }

        return $this->buildLocationFromDatabaseResult($result);
    }

    /**
     * @param string $name
     * @return Location|null
     */
    public function getLocationByName(string $name): ?Location
    {
        $result = DB::table(self::TABLE)
            ->select(self::SELECT)
            ->where(Launch::KEY_NAME, '=', $name)
            ->first();

        if ($result === null) {
            return null;
        }

        return $this->buildLocationFromDatabaseResult($result);
    }

    /**
     * @param string|null $name
     * @param string|null $countryCode
     * @param float|string|null $latitude
     * @param float|string|null $longitude
     * @return Location|null
     */
    public function createLocation(string $name, ?string $countryCode, $latitude, $longitude): ?Location
    {
        $id = DB::table(self::TABLE)
            ->insertGetId([
                Location::KEY_NAME => $name,
                Location::KEY_COUNTRY_CODE => $countryCode,
                Location::KEY_LATITUDE => $latitude,
                Location::KEY_LONGITUDE => $longitude,
            ]);

        return $this->getLocationByID($id);
    }

    /**
     * @param $result
     * @return Location
     */
    private function buildLocationFromDatabaseResult($result): Location
    {
        $location = new Location();

        if (isset($result->id)) {
            $location->setId($result->id);
        }

        if (isset($result->name)) {
            $location->setName($result->name);
        }

        if (isset($result->country_code)) {
            $location->setCountryCode($result->country_code);
        }

        if (isset($result->latitude)) {
            $location->setLatitude($result->latitude);
        }

        if (isset($result->longitude)) {
            $location->setLongitude($result->longitude);
        }

        return $location;
    }
}
