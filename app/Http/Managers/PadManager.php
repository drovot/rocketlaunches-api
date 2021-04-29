<?php

declare(strict_types=1);

namespace App\Http\Managers;

use App\Models\Location;
use App\Models\Pad;
use Illuminate\Support\Facades\DB;

class PadManager
{

    public const TABLE = 'rl_pad';

    public const SELECT = [
        Pad::KEY_ID,
        Pad::KEY_NAME,
        Pad::KEY_SLUG,
        Pad::KEY_IMAGE_URL,
        Pad::KEY_WIKI_URL,
        Pad::KEY_LOCATION_ID,
        Pad::KEY_ID,
    ];

    /** @var LocationManager */
    private LocationManager $locationManager;

    /**
     * PadManager constructor.
     */
    public function __construct()
    {
        $this->locationManager = new LocationManager();
    }

    /**
     * @param int|string $id
     * @return Pad|null
     */
    public function getPadById($id): ?Pad
    {
        $result = DB::table(self::TABLE)
            ->select(self::SELECT)
            ->where(Pad::KEY_ID, '=', $id)
            ->first();

        if ($result === null) {
            return null;
        }

        return $this->buildPadFromDatabaseResult($result);
    }

    /**
     * @param string $slug
     * @return Pad|null
     */
    public function getPadBySlug(string $slug): ?Pad
    {
        $result = DB::table(self::TABLE)
            ->select(self::SELECT)
            ->where(Pad::KEY_SLUG, '=', $slug)
            ->first();

        if ($result === null) {
            return null;
        }

        return $this->buildPadFromDatabaseResult($result);
    }

    /**
     * @return string|int
     */
    public function getTotalAmount()
    {
        return DB::table(self::TABLE)->selectRaw('COUNT(*) as total')->first()->total ?? 0;
    }

    /**
     * @param string $orderBy
     * @param string $orderMethod
     * @param int $limit
     * @param int $page
     * @return array
     */
    public function getPads(string $orderBy, string $orderMethod, int $limit, int $page): array
    {
        $providers = [];
        $result = DB::table(self::TABLE)
            ->select(self::SELECT)
            ->offset(($page - 1) * $limit)
            ->limit($limit)
            ->orderBy($orderBy, $orderMethod)
            ->get();

        foreach ($result as $item)
        {
            $providers[] = $this->buildPadFromDatabaseResult($item);
        }

        return $providers;
    }

    /**
     * @param $result
     * @return Pad
     */
    private function buildPadFromDatabaseResult($result): Pad
    {
        $pad = new Pad();

        if (isset($result->id)) {
            $pad->setId($result->id);
        }

        if (isset($result->name)) {
            $pad->setName($result->name);
        }

        if (isset($result->slug)) {
            $pad->setSlug($result->slug);
        }

        if (isset($result->wiki_url)) {
            $pad->setWikiURL($result->wiki_url);
        }

        if (isset($result->image_url)) {
            $pad->setImageURL($result->image_url);
        }

        if (isset($result->location_id)) {
            $this->locationManager->getLocationById($result->location_id);
        }

        return $pad;
    }

    /**
     * @param string $name
     * @param string|null $wikiURL
     * @param string|null $imageURL
     * @param Location|null $location
     * @return bool
     */
    public function createPad(
        string $name,
        ?string $wikiURL,
        ?string $imageURL,
        ?Location $location
    ): bool {
        $provider = $this->getPadBySlug(Utils::stringToSlug($name));

        if ($provider !== null) {
            return false;
        }

        return DB::table(self::TABLE)->insert([
            Pad::KEY_NAME => $name,
            Pad::KEY_SLUG => Utils::stringToSlug($name),
            Pad::KEY_WIKI_URL => $wikiURL,
            Pad::KEY_IMAGE_URL => $imageURL,
            Pad::KEY_LOCATION_ID => $location === null ? null : $location->getId()
        ]);
    }

    /**
     * @param $slug
     */
    public function deletePad($slug): void
    {
        DB::table(self::TABLE)->where(Pad::KEY_SLUG, '=', $slug)->delete();
    }

    /**
     * @param string $slug
     * @param string|null $name
     * @param string|null $wikiURL
     * @param string|null $imageURL
     * @param Location|null $location
     * @return bool
     */
    public function updatePad(
        string $slug,
        ?string $name,
        ?string $wikiURL,
        ?string $imageURL,
        ?Location $location
    ): bool {
        $pad = $this->getPadBySlug($slug);

        if ($pad === null) {
            return false;
        }

        DB::table(self::TABLE)
            ->where(Pad::KEY_SLUG, '=', $pad->getSlug())
            ->update(
                $this->buildUpdateArray(
                    $name,
                    $wikiURL,
                    $imageURL,
                    $location
                )
            );

        return true;
    }

    /**
     * @param string $name
     * @param string|null $wikiURL
     * @param string|null $imageURL
     * @param Location|null $location
     * @return array
     */
    public function buildUpdateArray(
        string $name,
        ?string $wikiURL,
        ?string $imageURL,
        ?Location $location
    ): array {
        $array = [];

        if ($name !== null) {
            $array[Pad::KEY_NAME] = $name;
        }

        if ($wikiURL !== null) {
            $array[Pad::KEY_WIKI_URL] = $wikiURL;
        }

        if ($imageURL !== null) {
            $array[Pad::KEY_IMAGE_URL] = $imageURL;
        }

        if ($location !== null) {
            $array[Pad::KEY_LOCATION_ID] = $location->getId();
        }

        return $array;
    }
}
