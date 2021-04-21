<?php

declare(strict_types=1);

namespace App\Http\Managers;

use App\Models\Launch;
use App\Models\Location;
use Illuminate\Support\Facades\DB;

class LocationManager
{

    public const TABLE = 'location';
    public const SELECT = [
        Location::KEY_ID,
        Location::KEY_SLUG,
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
     * @param string $slug
     * @return Location|null
     */
    public function getLocationBySlug(string $slug): ?Location
    {
        $result = DB::table(self::TABLE)
            ->select(self::SELECT)
            ->where(Launch::KEY_SLUG, '=', $slug)
            ->first();

        if ($result === null) {
            return null;
        }

        return $this->buildLocationFromDatabaseResult($result);
    }

    /**
     * @param string|null $name
     * @param string|null $countryCode
     * @param float|null $latitude
     * @param float|null $longitude
     * @return Location|null
     */
    public function createLocation(string $name, ?string $countryCode, ?float $latitude, ?float $longitude): ?Location
    {
        $slug = Utils::stringToSlug($name);

        DB::table(self::TABLE)
            ->insert([
                Location::KEY_NAME => $name,
                Location::KEY_SLUG => $slug,
                Location::KEY_COUNTRY_CODE => $countryCode,
                Location::KEY_LATITUDE => $latitude,
                Location::KEY_LONGITUDE => $longitude,
            ]);

        return $this->getLocationBySlug($slug);
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

        if (isset($result->slug)) {
            $location->setSlug($result->slug);
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
