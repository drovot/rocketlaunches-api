<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Response\Response;
use App\Http\Search\SearchManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController
{

    private SearchManager $searchManager;

    public function __construct()
    {
        $this->searchManager = new SearchManager();
    }

    public function advancedSearch(Request $request): JsonResponse
    {
        $query = $request->has("query") ? $request->get("query") : null;
        $search = $this->searchManager->advancedSearch($query);
        $response = new Response();

        if ($search === null) {
            return $response->setStatusCode(204)->build();
        }

        return $response->setResult($search)->build();
    }
}
