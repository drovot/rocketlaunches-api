<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Utils\HasNameSlug;
use App\Models\Utils\HasId;

class Location extends AbstractModel
{

    public const KEY_ID = 'id';
    public const KEY_NAME = 'name';
    public const KEY_SLUG = 'slug';
    public const KEY_COUNTRY_CODE = 'country_code';
    public const KEY_LATITUDE = 'latitude';
    public const KEY_LONGITUDE = 'longitude';

    use HasId;
    use HasNameSlug;

    /** @var string */
    private string $countryCode;

    /** @var float */
    private float $latitude;

    /** @var float */
    private float $longitude;

    /**
     * @return array
     */
    public function export(): array
    {
        return [
            self::KEY_ID => $this->id,
            self::KEY_NAME => $this->name,
            self::KEY_COUNTRY_CODE => $this->countryCode,
            self::KEY_LATITUDE => $this->latitude,
            self::KEY_LONGITUDE => $this->longitude
        ];
    }

    /**
     * @return string
     */
    public function getCountryCode(): string
    {
        return $this->countryCode;
    }

    /**
     * @param string $countryCode
     * @return self
     */
    public function setCountryCode(string $countryCode): self
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    /**
     * @return float
     */
    public function getLatitude(): float
    {
        return $this->latitude;
    }

    /**
     * @param float $latitude
     * @return Location
     */
    public function setLatitude(float $latitude): self
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * @return float
     */
    public function getLongitude(): float
    {
        return $this->longitude;
    }

    /**
     * @param float $longitude
     * @return Location
     */
    public function setLongitude(float $longitude): self
    {
        $this->longitude = $longitude;

        return $this;
    }
}
