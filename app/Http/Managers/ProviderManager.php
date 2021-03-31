<?php

declare(strict_types=1);

namespace App\Http\Managers;

use App\Models\Provider;
use Illuminate\Support\Facades\DB;

class ProviderManager
{

    public const TABLE = "rl_provider";

    /**
     * @param int|string $id
     * @return Provider|null
     */
    public function getProviderById($id): ?Provider
    {
        $result = DB::table(self::TABLE)
            ->select([
                "id", "name", "slug", "abbreviation", "wikiURL", "imageURL", "logoURL"
            ])
            ->where("id", "=", $id)
            ->first();

        if ($result === null) {
            return null;
        }

        return $this->buildProviderFromDatabaseResult($result);
    }

    /**
     * @param string $slug
     * @return Provider|null
     */
    public function getProviderBySlug(string $slug): ?Provider
    {
        $result = DB::table(self::TABLE)
            ->select([
                "id", "name", "slug", "abbreviation", "wikiURL", "imageURL", "logoURL"
            ])
            ->where("slug", "=", $slug)
            ->first();

        if ($result === null) {
            return null;
        }

        return $this->buildProviderFromDatabaseResult($result);
    }

    /**
     * @param string $orderBy
     * @param string $orderMethod
     * @param int $limit
     * @param int $page
     * @return array
     */
    public function getProviders(string $orderBy, string $orderMethod, int $limit, int $page): array
    {
        $providers = [];
        $result = DB::table(self::TABLE)
            ->select([
                "id", "name", "slug", "abbreviation", "wikiURL", "imageURL", "logoURL"
            ])
            ->offset(($page - 1) * $limit)
            ->limit($limit)
            ->orderBy($orderBy, $orderMethod)
            ->get();

        foreach ($result as $item)
        {
            $providers[] = $this->buildProviderFromDatabaseResult($item);
        }

        return $providers;
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
     * @return Provider
     */
    private function buildProviderFromDatabaseResult($result): Provider
    {
        $provider = new Provider();

        if (isset($result->id)) {
            $provider->setId($result->id);
        }

        if (isset($result->name)) {
            $provider->setName($result->name);
        }

        if (isset($result->slug)) {
            $provider->setSlug($result->slug);
        }

        if (isset($result->abbreviation)) {
            $provider->setAbbreviation($result->abbreviation);
        }

        if (isset($result->wikiURL)) {
            $provider->setWikiURL($result->wikiURL);
        }

        if (isset($result->imageURL)) {
            $provider->setImageURL($result->imageURL);
        }

        if (isset($result->logoURL)) {
            $provider->setLogoURL($result->logoURL);
        }

        return $provider;
    }

    public function createProvider(
        string $name,
        ?string $abbreviation,
        ?string $wikiURL,
        ?string $imageURL,
        ?string $logoURL
    ): bool {
        $provider = $this->getProviderBySlug(Utils::stringToSlug($name));

        if ($provider !== null) {
            return false;
        }

        return DB::table(self::TABLE)->insert([
            "name" => $name,
            "slug" => Utils::stringToSlug($name),
            "abbreviation" => $abbreviation,
            "wikiURL" => $wikiURL,
            "imageURL" => $imageURL,
            "logoURL" => $logoURL,
        ]);
    }

    /**
     * @param $slug
     */
    public function deleteProvider($slug): void
    {
        DB::table(self::TABLE)->where("slug", "=", $slug)->delete();
    }

    /**
     * @param string $slug
     * @param string|null $name
     * @param string|null $abbreviation
     * @param string|null $wikiURL
     * @param string|null $imageURL
     * @param string|null $logoURL
     * @return bool
     */
    public function updateProvider(
        string $slug,
        ?string $name,
        ?string $abbreviation,
        ?string $wikiURL,
        ?string $imageURL,
        ?string $logoURL
    ): bool {
        $provider = $this->getProviderBySlug($slug);

        if ($provider === null) {
            return false;
        }

        DB::table(self::TABLE)
            ->where("slug", "=", $slug)
            ->update(
                $this->buildUpdateArray(
                    $name,
                    $abbreviation,
                    $wikiURL,
                    $imageURL,
                    $logoURL
                )
            );
        return true;
    }

    /**
     * @param string $name
     * @param string|null $abbreviation
     * @param string|null $wikiURL
     * @param string|null $imageURL
     * @param string|null $logoURL
     * @return array
     */
    public function buildUpdateArray(
        string $name,
        ?string $abbreviation,
        ?string $wikiURL,
        ?string $imageURL,
        ?string $logoURL
    ): array {
        $array = [];

        if ($name !== null) {
            $array['name'] = $name;
        }

        if ($abbreviation !== null) {
            $array['abbreviation'] = $abbreviation;
        }

        if ($wikiURL !== null) {
            $array['wikiURL'] = $wikiURL;
        }

        if ($imageURL !== null) {
            $array['imageURL'] = $imageURL;
        }

        if ($logoURL !== null) {
            $array['logoURL'] = $logoURL;
        }

        return $array;
    }
}
