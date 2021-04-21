<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Managers\Defaults;
use App\Http\Managers\PadManager;
use App\Http\Managers\Utils;
use App\Http\Response\Response;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Laravel\Lumen\Routing\Controller as BaseController;

class PadController extends BaseController
{

    /**
     * @var PadManager
     */
    private PadManager $padManager;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->padManager = new PadManager();
    }

    /**
     * @param string $pad
     * @param Request $request
     * @return JsonResponse
     */
    public function getPad(string $pad, Request $request): JsonResponse
    {
        $response = new Response();
        $result = $this->padManager->getPadBySlug($pad);

        $trackingId = $request->attributes->has('tracking_id') ? $request->attributes->get('tracking_id') : null;
        $response->setTrackingId($trackingId);

        if ($result === null) {
            return $response->setStatusCode(404)->build();
        }

        return $response->setResult($result)->build();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getPads(Request $request): JsonResponse
    {
        $response = new Response();

        // parameters
        $limit = $request->has("limit") ? (int) $request->get("limit") : Defaults::REQUEST_LIMIT;
        $page = $request->has("page") ? (int) $request->get("page") : Defaults::REQUEST_PAGE;

        $trackingId = $request->attributes->has('tracking_id') ? $request->attributes->get('tracking_id') : null;
        $response->setTrackingId($trackingId);

        $providers = $this->padManager->getPads(
            Defaults::DATABASE_COLUMN_CREATED,
            Defaults::DATABASE_ORDER_DESC,
            $limit,
            $page
        );

        if ($providers === null) {
            return $response->setStatusCode(204)->build();
        }

        return $response->setTotal($this->padManager->getTotalAmount())->setResult($providers)->build();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function createPad(Request $request): JsonResponse
    {
        $response = new Response();

        $name = $request->has("name") ? $request->get("name") : null;
        $wikiURL = $request->has("wikiURL") ? $request->get("wikiURL") : null;
        $imageURL = $request->has("imageURL") ? $request->get("imageURL") : null;

        $trackingId = $request->attributes->has('tracking_id') ? $request->attributes->get('tracking_id') : null;
        $response->setTrackingId($trackingId);

        $success = $this->padManager->createPad(
            $name,
            $wikiURL,
            $imageURL,
        );

        if (!$success) {
            return $response->setStatusCode(100)->setErrorMessage("Already exists")->build();
        }

        $result = $this->padManager->getPadBySlug(Utils::stringToSlug($name));

        return $response->setResult($result)->build();
    }

    /**
     * @param $pad
     * @param Request $request
     * @return JsonResponse
     */
    public function updatePad($pad, Request $request): JsonResponse
    {
        $response = new Response();

        $name = $request->has("name") ? $request->get("name") : null;
        $wikiURL = $request->has("wikiURL") ? $request->get("wikiURL") : null;
        $imageURL = $request->has("imageURL") ? $request->get("imageURL") : null;

        $trackingId = $request->attributes->has('tracking_id') ? $request->attributes->get('tracking_id') : null;
        $response->setTrackingId($trackingId);

        $success = $this->padManager->updatePad(
            $pad,
            $name,
            $wikiURL,
            $imageURL,
        );

        if (!$success) {
            return $response->setStatusCode(404)->build();
        }

        $result = $this->padManager->getPadBySlug($pad);
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

        $result = $this->padManager->getPadBySlug($provider);

        if ($result === null) {
            return $response->setStatusCode(404)->build();
        }

        $this->padManager->deletePad($provider);
        return $response->build();
    }
}
