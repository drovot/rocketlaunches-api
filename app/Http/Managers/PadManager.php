<?php

declare(strict_types=1);

namespace App\Http\Managers;

use App\Models\Pad;
use Illuminate\Support\Facades\DB;

class PadManager
{

    public const TABLE = "rl_pad";

    /**
     * @param int|string $id
     * @return Pad|null
     */
    public function getPadById($id): ?Pad
    {
        $result = DB::table(self::TABLE)
            ->select([
                "id", "name", "slug", "wikiURL", "imageURL"
            ])
            ->where("id", "=", $id)
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
            ->select([
                "id", "name", "slug", "wikiURL", "imageURL"
            ])
            ->where("slug", "=", $slug)
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
        return DB::table(self::TABLE)->selectRaw("COUNT(*) as total")->first()->total ?? 0;
    }

    public function getPads(string $orderBy, string $orderMethod, int $limit, int $page): array
    {
        $providers = [];
        $result = DB::table(self::TABLE)
            ->select([
                "id", "name", "slug", "wikiURL", "imageURL"
            ])
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

        if (isset($result->wikiURL)) {
            $pad->setWikiURL($result->wikiURL);
        }

        if (isset($result->imageURL)) {
            $pad->setImageURL($result->imageURL);
        }

        return $pad;
    }

    public function createPad(
        string $name,
        ?string $wikiURL,
        ?string $imageURL
    ): bool {
        $provider = $this->getPadBySlug(Utils::stringToSlug($name));

        if ($provider !== null) {
            return false;
        }

        return DB::table(self::TABLE)->insert([
            "name" => $name,
            "slug" => Utils::stringToSlug($name),
            "wikiURL" => $wikiURL,
            "imageURL" => $imageURL,
        ]);
    }

    /**
     * @param $slug
     */
    public function deletePad($slug): void
    {
        DB::table(self::TABLE)->where("slug", "=", $slug)->delete();
    }

    /**
     * @param string $slug
     * @param string|null $name
     * @param string|null $wikiURL
     * @param string|null $imageURL
     * @return bool
     */
    public function updatePad(
        string $slug,
        ?string $name,
        ?string $wikiURL,
        ?string $imageURL
    ): bool {
        $provider = $this->getPadBySlug($slug);

        if ($provider === null) {
            return false;
        }

        DB::table(self::TABLE)
            ->where("slug", "=", $slug)
            ->update(
                $this->buildUpdateArray(
                    $name,
                    $wikiURL,
                    $imageURL
                )
            );
        return true;
    }

    /**
     * @param string $name
     * @param string|null $wikiURL
     * @param string|null $imageURL
     * @return array
     */
    public function buildUpdateArray(
        string $name,
        ?string $wikiURL,
        ?string $imageURL
    ): array {
        $array = [];

        if ($name !== null) {
            $array['name'] = $name;
        }

        if ($wikiURL !== null) {
            $array['wikiURL'] = $wikiURL;
        }

        if ($imageURL !== null) {
            $array['imageURL'] = $imageURL;
        }

        return $array;
    }
}
