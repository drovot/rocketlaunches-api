<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Managers\Defaults;
use App\Http\Managers\RocketManager;
use App\Http\Managers\Utils;
use App\Http\Response\Response;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Laravel\Lumen\Routing\Controller as BaseController;

class RocketController extends BaseController
{

    /**
     * @var RocketManager
     */
    private RocketManager $rocketManager;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->rocketManager = new RocketManager();
    }

    /**
     * @param string $rocket
     * @param Request $request
     * @return JsonResponse
     */
    public function getRocket(string $rocket, Request $request): JsonResponse
    {
        $response = new Response();

        $result = $this->rocketManager->getRocketBySlug($rocket);

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
    public function getRockets(Request $request): JsonResponse
    {
        $response = new Response();

        // parameters
        $limit = $request->has("limit") ? (int) $request->get("limit") : Defaults::REQUEST_LIMIT;
        $page = $request->has("page") ? (int) $request->get("page") : Defaults::REQUEST_PAGE;

        $trackingId = $request->attributes->has('tracking_id') ? $request->attributes->get('tracking_id') : null;
        $response->setTrackingId($trackingId);

        $rockets = $this->rocketManager->getRockets(
            Defaults::DATABASE_COLUMN_CREATED,
            Defaults::DATABASE_ORDER_DESC,
            $limit,
            $page
        );

        if ($rockets === null) {
            return $response->setStatusCode(204)->build();
        }

        return $response->setTotal($this->rocketManager->getTotalAmount())->setResult($rockets)->build();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function createRocket(Request $request): JsonResponse
    {
        $response = new Response();

        $name = $request->has("name") ? $request->get("name") : null;
        $wikiURL = $request->has("wikiURL") ? $request->get("wikiURL") : null;
        $imageURL = $request->has("imageURL") ? $request->get("imageURL") : null;

        $trackingId = $request->attributes->has('tracking_id') ? $request->attributes->get('tracking_id') : null;
        $response->setTrackingId($trackingId);

        $success = $this->rocketManager->createRocket(
            $name,
            $wikiURL,
            $imageURL
        );

        if (!$success) {
            return $response->setStatusCode(100)->setErrorMessage("Already exists")->build();
        }

        $result = $this->rocketManager->getRocketBySlug(Utils::stringToSlug($name));

        return $response->setResult($result)->build();
    }

    /**
     * @param $rocket
     * @param Request $request
     * @return JsonResponse
     */
    public function updateRocket($rocket, Request $request): JsonResponse
    {
        $response = new Response();

        $name = $request->has("name") ? $request->get("name") : null;
        $imageURL = $request->has("imageURL") ? $request->get("imageURL") : null;
        $wikiURL = $request->has("wikiURL") ? $request->get("wikiURL") : null;

        $trackingId = $request->attributes->has('tracking_id') ? $request->attributes->get('tracking_id') : null;
        $response->setTrackingId($trackingId);

        $success = $this->rocketManager->updateRocket(
            $rocket,
            $name,
            $wikiURL,
            $imageURL
        );

        if (!$success) {
            return $response->setStatusCode(404)->build();
        }

        $result = $this->rocketManager->getRocketBySlug($rocket);
        return $response->setResult($result)->build();
    }

    /**
     * @param $rocket
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteRocket($rocket, Request $request): JsonResponse
    {
        $response = new Response();

        $trackingId = $request->attributes->has('tracking_id') ? $request->attributes->get('tracking_id') : null;
        $response->setTrackingId($trackingId);

        $result = $this->rocketManager->getRocketBySlug($rocket);

        if ($result === null) {
            return $response->setStatusCode(404)->build();
        }

        $this->rocketManager->deleteRocket($rocket);
        return $response->build();
    }
}
