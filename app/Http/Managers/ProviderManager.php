<?php

declare(strict_types=1);

namespace App\Http\Managers;

use App\Models\Provider;
use Illuminate\Support\Facades\DB;

class ProviderManager
{

    public const TABLE = 'rl_provider';

    public const SELECT = [
        Provider::KEY_ID,
        Provider::KEY_SLUG,
        Provider::KEY_ABBREVIATION,
        Provider::KEY_WIKI_URL,
        Provider::KEY_IMAGE_URL,
        Provider::KEY_LOGO_URL
    ];

    /**
     * @param int|string $id
     * @return Provider|null
     */
    public function getProviderById($id): ?Provider
    {
        $result = DB::table(self::TABLE)
            ->select(self::SELECT)
            ->where(Provider::KEY_ID, '=', $id)
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
            ->select(self::SELECT)
            ->where(Provider::KEY_SLUG, '=', $slug)
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
            ->select(self::SELECT)
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
        return DB::table(self::TABLE)->selectRaw('COUNT(*) as total')->first()->total ?? 0;
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

        if (isset($result->wiki_url)) {
            $provider->setWikiURL($result->wiki_url);
        }

        if (isset($result->image_url)) {
            $provider->setImageURL($result->image_url);
        }

        if (isset($result->logo_url)) {
            $provider->setLogoURL($result->logo_url);
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
            Provider::KEY_NAME => $name,
            Provider::KEY_SLUG => Utils::stringToSlug($name),
            Provider::KEY_ABBREVIATION => $abbreviation,
            Provider::KEY_WIKI_URL => $wikiURL,
            Provider::KEY_IMAGE_URL => $imageURL,
            Provider::KEY_LOGO_URL => $logoURL,
        ]);
    }

    /**
     * @param $slug
     */
    public function deleteProvider($slug): void
    {
        DB::table(self::TABLE)->where(Provider::KEY_SLUG, "=", $slug)->delete();
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
            ->where(Provider::KEY_SLUG, '=', $slug)
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
     * @param string|null $name
     * @param string|null $abbreviation
     * @param string|null $wikiURL
     * @param string|null $imageURL
     * @param string|null $logoURL
     * @return array
     */
    public function buildUpdateArray(
        ?string $name,
        ?string $abbreviation,
        ?string $wikiURL,
        ?string $imageURL,
        ?string $logoURL
    ): array {
        $array = [];

        if ($name !== null) {
            $array[Provider::KEY_NAME] = $name;
        }

        if ($abbreviation !== null) {
            $array[Provider::KEY_SLUG] = $abbreviation;
        }

        if ($wikiURL !== null) {
            $array[Provider::KEY_WIKI_URL] = $wikiURL;
        }

        if ($imageURL !== null) {
            $array[Provider::KEY_IMAGE_URL] = $imageURL;
        }

        if ($logoURL !== null) {
            $array[Provider::KEY_LOGO_URL] = $logoURL;
        }

        return $array;
    }
}
