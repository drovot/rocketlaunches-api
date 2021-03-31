<?php

declare(strict_types=1);

namespace App\Http\Managers;

use App\Models\Rocket;
use Illuminate\Support\Facades\DB;

class RocketManager
{

    public const TABLE = "rl_rocket";

    private const SELECT = [
        "id", "name", "slug", "imageURL", "wikiURL"
    ];

    /**
     * @param int|string $id
     * @return Rocket|null
     */
    public function getRocketById($id): ?Rocket
    {
        $result = DB::table(self::TABLE)
            ->select(self::SELECT)
            ->where("id", "=", $id)
            ->first();

        if ($result === null) {
            return null;
        }

        return $this->buildRocketFromDatabaseResult($result);
    }

    /**
     * @param string $slug
     * @return Rocket|null
     */
    public function getRocketBySlug(string $slug): ?Rocket
    {
        $result = DB::table(self::TABLE)
            ->select(self::SELECT)
            ->where("slug", "=", $slug)
            ->first();

        if ($result === null) {
            return null;
        }

        return $this->buildRocketFromDatabaseResult($result);
    }

    /**
     * @param string $orderBy
     * @param string $orderMethod
     * @param int $limit
     * @param int $page
     * @return array
     */
    public function getRockets(string $orderBy, string $orderMethod, int $limit, int $page): array
    {
        $rockets = [];
        $result = DB::table(self::TABLE)
            ->select(self::SELECT)
            ->offset(($page - 1) * $limit)
            ->limit($limit)
            ->orderBy($orderBy, $orderMethod)
            ->get();

        foreach ($result as $item)
        {
            $rockets[] = $this->buildRocketFromDatabaseResult($item);
        }

        return $rockets;
    }

    /**
     * @param string|null $name
     * @param string|null $wikiURL
     * @param string|null $imageURL
     * @return bool
     */
    public function createRocket(
        ?string $name,
        ?string $wikiURL,
        ?string $imageURL
    ): bool {
        $rocket = $this->getRocketBySlug(Utils::stringToSlug($name));

        if ($rocket !== null) {
            return false;
        }

        return DB::table(self::TABLE)->insert([
            "name" => $name,
            "slug" => Utils::stringToSlug($name),
            "wikiURL" => $wikiURL,
            "imageURL" => $imageURL
        ]);
    }

    /**
     * @param string $slug
     * @param string|null $name
     * @param string|null $wikiURL
     * @param string|null $imageURL
     * @return bool
     */
    public function updateRocket(
        string $slug,
        ?string $name,
        ?string $wikiURL,
        ?string $imageURL
    ): bool {
        $rocket = $this->getRocketBySlug($slug);

        if ($rocket === null) {
            return false;
        }

        DB::table(self::TABLE)->where("slug", "=", $slug)->update($this->buildUpdateArray(
            $name,
            $imageURL,
            $wikiURL
        ));
        return true;
    }

    /**
     * @param string $slug
     * @return void
     */
    public function deleteRocket(string $slug): void {
        $rocket = $this->getRocketBySlug($slug);

        if ($rocket === null) {
            return;
        }

        DB::table(self::TABLE)->where("slug", "=", $slug)->delete();
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
     * @return Rocket
     */
    private function buildRocketFromDatabaseResult($result): Rocket
    {
        $rocket = new Rocket();

        if (isset($result->id)) {
            $rocket->setId($result->id);
        }

        if (isset($result->name)) {
            $rocket->setName($result->name);
        }

        if (isset($result->slug)) {
            $rocket->setSlug($result->slug);
        }

        if (isset($result->wikiURL)) {
            $rocket->setWikiURL($result->wikiURL);
        }

        if (isset($result->imageURL)) {
            $rocket->setImageURL($result->imageURL);
        }

        return $rocket;
    }

    /**
     * @param string|null $name
     * @param string|null $imageURL
     * @param string|null $wikiURL
     * @return array
     */
    private function buildUpdateArray(
        ?string $name,
        ?string $imageURL,
        ?string $wikiURL
    ): array {
        $array = [];

        if ($name !== null) {
            $array["name"] = $name;
        }

        if ($imageURL !== null) {
            $array["imageURL"] = $imageURL;
        }

        if ($wikiURL !== null) {
            $array["wikiURL"] = $wikiURL;
        }

        return $array;
    }
}
