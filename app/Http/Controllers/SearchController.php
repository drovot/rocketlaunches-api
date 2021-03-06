<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Response\Response;
use App\Http\Search\SearchManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;

class SearchController extends BaseController
{

    private SearchManager $searchManager;

    public function __construct()
    {
        $this->searchManager = new SearchManager();
    }

    public function advancedSearch(Request $request): JsonResponse
    {
        $query = $request->has("query") ? $request->get("query") : null;
        $type = $request->has("type") ? $request->get("type") : null;
        $search = $this->searchManager->advancedSearch($query, $type);
        $response = new Response();

        $trackingId = $request->attributes->has('tracking_id') ? $request->attributes->get('tracking_id') : null;
        $response->setTrackingId($trackingId);

        if ($search === null) {
            return $response->setStatusCode(204)->build();
        }

        return $response->setResult($search)->build();
    }
}
