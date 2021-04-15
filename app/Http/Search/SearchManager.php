<?php

declare(strict_types=1);

namespace App\Http\Search;

use App\Http\Managers\LaunchManager;
use App\Http\Managers\PadManager;
use App\Http\Managers\ProviderManager;
use App\Http\Managers\RocketManager;
use App\Models\AbstractModel;
use Illuminate\Support\Facades\DB;
use function PHPUnit\Framework\isEmpty;

class SearchManager
{

    private const TYPE_LAUNCH = 'launch';
    private const TYPE_ROCKET = 'rocket';
    private const TYPE_PROVIDER = 'provider';
    private const TYPE_PAD = 'pad';

    private const TYPES = [
        self::TYPE_LAUNCH,
        self::TYPE_ROCKET,
        self::TYPE_PROVIDER,
        self::TYPE_PAD
    ];

    /**
     * @param string $query
     * @param string|null $typeOnly
     * @return array|null
     */
    public function advancedSearch(string $query, ?string $typeOnly = null): ?array
    {
        $searchResponses = [];

        if ($typeOnly !== null) {
            return $this->searchByType($typeOnly, $query);
        }

        foreach (self::TYPES as $type) {
            $result = $this->searchByType($type, $query);

            if ($result === null) {
                continue;
            }

            foreach ($result as $item) {
                $searchResponses[] = $item;
            }
        }

        return $searchResponses;
    }

    /**
     * @param string $type
     * @param string $query
     * @return SearchResponse[]|array|null
     */
    private function searchByType(string $type, string $query, $admin = false): ?array
    {
        $searchResponses = [];
        $response = null;

        switch ($type) {
            case self::TYPE_LAUNCH:
                $response = DB::table(LaunchManager::TABLE)->where("name", "LIKE", "%" . strtolower($query) . "%")
                    ->orWhere("tags", "LIKE", "%" . strtolower($query) . "%")->orWhere("description", "LIKE", "%" . strtolower($query) . "%")->get();
                break;
            case self::TYPE_ROCKET:
                $response = DB::table(RocketManager::TABLE)->where("name", "LIKE", "%" . strtolower($query) . "%")->get();
                break;
            case self::TYPE_PROVIDER:
                $response = DB::table(ProviderManager::TABLE)->where("name", "LIKE", "%" . strtolower($query) . "%")->get();
                break;
            case self::TYPE_PAD:
                $response = DB::table(PadManager::TABLE)->where("name", "LIKE", "%" . strtolower($query) . "%")->get();
                break;
            default:
                $response = null;
        }

        if ($response === null) {
            return null;
        }

        foreach ($response as $item) {
            $searchResponse = new SearchResponse();
            $searchResponse->setCategory($type);
            $searchResponse->setSlug($item->slug);
            $searchResponse->setPath("/$type/" . $item->slug);
            $searchResponse->setImageURL($item->imageURL ?? null);
            $searchResponse->setTitle($item->name ?? "");
            $searchResponse->setSubtitle($item->description ?? "");

            $searchResponses[] = $searchResponse;
        }

        return empty($searchResponses) ? null : $searchResponses;
    }
}
