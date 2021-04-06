<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Managers\Defaults;
use App\Http\Managers\ProviderManager;
use App\Http\Managers\Utils;
use App\Http\Response\Response;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProviderController extends Controller
{

    /**
     * @var ProviderManager
     */
    private ProviderManager $providerManager;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->providerManager = new ProviderManager();
    }

    /**
     * @param string $provider
     * @param Request $request
     * @return JsonResponse
     */
    public function getProvider(string $provider, Request $request): JsonResponse
    {
        $response = new Response();

        $trackingId = $request->attributes->has('tracking_id') ? $request->attributes->get('tracking_id') : null;
        $response->setTrackingId($trackingId);

        $result = $this->providerManager->getProviderBySlug($provider);

        if ($result === null) {
            return $response->setStatusCode(404)->build();
        }

        return $response->setResult($result)->build();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getProviders(Request $request): JsonResponse
    {
        $response = new Response();

        // parameters
        $limit = $request->has("limit") ? (int) $request->get("limit") : Defaults::REQUEST_LIMIT;
        $page = $request->has("page") ? (int) $request->get("page") : Defaults::REQUEST_PAGE;

        $trackingId = $request->attributes->has('tracking_id') ? $request->attributes->get('tracking_id') : null;
        $response->setTrackingId($trackingId);

        $providers = $this->providerManager->getProviders(
            Defaults::DATABASE_COLUMN_CREATED,
            Defaults::DATABASE_ORDER_DESC,
            $limit,
            $page
        );

        if ($providers === null) {
            return $response->setStatusCode(204)->build();
        }

        return $response->setTotal($this->providerManager->getTotalAmount())->setResult($providers)->build();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function createProvider(Request $request): JsonResponse
    {
        $response = new Response();

        $name = $request->has("name") ? $request->get("name") : null;
        $abbreviation = $request->has("abbreviation") ? $request->get("abbreviation") : null;
        $wikiURL = $request->has("wikiURL") ? $request->get("wikiURL") : null;
        $imageURL = $request->has("imageURL") ? $request->get("imageURL") : null;
        $logoURL = $request->has("logoURL") ? $request->get("logoURL") : null;

        $trackingId = $request->attributes->has('tracking_id') ? $request->attributes->get('tracking_id') : null;
        $response->setTrackingId($trackingId);

        $success = $this->providerManager->createProvider(
            $name,
            $abbreviation,
            $wikiURL,
            $imageURL,
            $logoURL
        );

        if (!$success) {
            return $response->setStatusCode(100)->setErrorMessage("Already exists")->build();
        }

        $result = $this->providerManager->getProviderBySlug(Utils::stringToSlug($name));

        return $response->setResult($result)->build();
    }

    /**
     * @param $provider
     * @param Request $request
     * @return JsonResponse
     */
    public function updateProvider($provider, Request $request): JsonResponse
    {
        $response = new Response();

        $name = $request->has("name") ? $request->get("name") : null;
        $abbreviation = $request->has("abbreviation") ? $request->get("abbreviation") : null;
        $wikiURL = $request->has("wikiURL") ? $request->get("wikiURL") : null;
        $imageURL = $request->has("imageURL") ? $request->get("imageURL") : null;
        $logoURL = $request->has("logoURL") ? $request->get("logoURL") : null;

        $trackingId = $request->attributes->has('tracking_id') ? $request->attributes->get('tracking_id') : null;
        $response->setTrackingId($trackingId);

        $success = $this->providerManager->updateProvider(
            $provider,
            $name,
            $abbreviation,
            $wikiURL,
            $imageURL,
            $logoURL
        );

        if (!$success) {
            return $response->setStatusCode(404)->build();
        }

        $result = $this->providerManager->getProviderBySlug($provider);
        return $response->setResult($result)->build();
    }

    /**
     * @param string $provider
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteProvider(string $provider, Request $request): JsonResponse
    {
        $response = new Response();

        $trackingId = $request->attributes->has('tracking_id') ? $request->attributes->get('tracking_id') : null;
        $response->setTrackingId($trackingId);

        $result = $this->providerManager->getProviderBySlug($provider);

        if ($result === null) {
            return $response->setStatusCode(404)->build();
        }

        $this->providerManager->deleteProvider($provider);
        return $response->build();
    }
}
