<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Managers\Defaults;
use App\Http\Managers\LaunchManager;
use App\Http\Managers\PadManager;
use App\Http\Managers\ProviderManager;
use App\Http\Managers\RocketManager;
use App\Http\Managers\StatusManager;
use App\Http\Managers\Utils;
use App\Http\Response\Response;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LaunchController extends Controller
{

    /**
     * @var LaunchManager
     */
    private LaunchManager $launchManager;

    /**
     * @var RocketManager
     */
    private RocketManager $rocketManager;

    /**
     * @var ProviderManager
     */
    private ProviderManager $providerManager;

    /**
     * @var PadManager
     */
    private PadManager $padManager;

    /**
     * @var StatusManager
     */
    private StatusManager $statusManager;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->launchManager = new LaunchManager();
        $this->rocketManager = new RocketManager();
        $this->providerManager = new ProviderManager();
        $this->padManager = new PadManager();
        $this->statusManager = new StatusManager();
    }

    /**
     * @param string $launch
     * @param Request $request
     * @return JsonResponse
     */
    public function getLaunch(string $launch, Request $request): JsonResponse
    {
        $response = new Response();

        $detailed = $request->has("detailed") ? (bool) $request->get("detailed") : Defaults::REQUEST_DETAILED;

        $result = $this->launchManager->getLaunchBySlug($launch, $detailed);

        if ($result === null || !$result->isPublished()) {
            return $response->setStatusCode(404)->build();
        }

        return $response->setResult($result)->build();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws \JsonException
     */
    public function createLaunch(Request $request): JsonResponse
    {
        $response = new Response();

        $name = $request->has("name") ? $request->get("name") : null;
        $description = $request->has("description") ? $request->get("description") : null;
        $rocket = $request->has("rocket") ? $this->rocketManager->getRocketBySlug($request->get("rocket")) : null;
        $provider = $request->has("provider") ? $this->providerManager->getProviderBySlug($request->get("provider")) : null;
        $pad = $request->has("pad") ? $this->padManager->getPadBySlug($request->get("pad")) : null;
        $launchStatus = $request->has("status") ? $this->statusManager->getStatusByDisplayName($request->get("status")) : null;

        $success = $this->launchManager->createLaunch(
            $name,
            $description,
            $rocket,
            $pad,
            $provider,
            $launchStatus,
            null,
            [],
            null
        );

        if (!$success) {
            return $response->setStatusCode(100)->setErrorMessage("Already exists")->build();
        }

        $result = $this->launchManager->getLaunchBySlug(Utils::stringToSlug($name), true);

        return $response->setResult($result)->build();
    }

    /**
     * @param $launch
     * @param Request $request
     * @return JsonResponse
     */
    public function updateLaunch($launch, Request $request): JsonResponse
    {
        $response = new Response();

        $name = $request->has("name") ? $request->get("name") : null;
        $description = $request->has("description") ? $request->get("description") : null;
        $rocket = $request->has("rocket") ? $this->rocketManager->getRocketBySlug($request->get("rocket")) : null;
        $provider = $request->has("provider") ? $this->providerManager->getProviderBySlug($request->get("provider")) : null;
        $pad = $request->has("pad") ? $this->padManager->getPadBySlug($request->get("pad")) : null;
        $livestream = $request->has("livestream") ? $request->get("livestream") : null;
        $launchStatus = $request->has("status") ? $this->statusManager->getStatusByDisplayName($request->get("status")) : null;
        $published = $request->has("published") ? (bool) $request->get("published") : null;

        $success = $this->launchManager->updateLaunch(
            $launch,
            $name,
            $description,
            $rocket,
            $pad,
            $provider,
            $launchStatus,
            $livestream,
            [],
            null,
            $published
        );

        if (!$success) {
            return $response->setStatusCode(404)->build();
        }

        $result = $this->launchManager->getLaunchBySlug($launch, true);
        return $response->setResult($result)->build();
    }

    /**
     * @param string $launch
     * @return JsonResponse
     */
    public function deleteLaunch(string $launch): JsonResponse
    {
        $response = new Response();

        $result = $this->launchManager->getLaunchBySlug($launch);

        if ($result === null) {
            return $response->setStatusCode(404)->build();
        }

        $this->launchManager->deleteLaunch($launch);
        return $response->build();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getLaunches(Request $request): JsonResponse
    {
        $response = new Response();

        // parameters
        $limit = $request->has("limit") ? (int) $request->get("limit") : Defaults::REQUEST_LIMIT;
        $page = $request->has("page") ? (int) $request->get("page") : Defaults::REQUEST_PAGE;
        $detailed = $request->has("detailed") ? (bool) $request->get("detailed") : Defaults::REQUEST_DETAILED;

        $launches = $this->launchManager->getLaunchesAdmin(
            $limit,
            $page,
            $detailed
        );

        if ($launches === null) {
            return $response->setStatusCode(204)->build();
        }

        return $response->setTotal($this->launchManager->getTotalAmount())->setResult($launches)->build();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getPreviousLaunches(Request $request): JsonResponse
    {
        $response = new Response();

        // parameters
        $limit = $request->has("limit") ? (int) $request->get("limit") : Defaults::REQUEST_LIMIT;
        $page = $request->has("page") ? (int) $request->get("page") : Defaults::REQUEST_PAGE;
        $detailed = $request->has("detailed") ? (bool) $request->get("detailed") : Defaults::REQUEST_DETAILED;

        if (!is_bool($detailed)) {
            $detailed = Defaults::REQUEST_DETAILED;
        }

        $launches = $this->launchManager->getLaunches(
            false,
            Defaults::DATABASE_COLUMN_START_NET,
            Defaults::DATABASE_ORDER_DESC,
            $limit,
            $page,
            $detailed
        );

        if ($launches === null) {
            return $response->setStatusCode(204)->build();
        }

        return $response->setTotal($this->launchManager->getTotalAmount(LaunchManager::KEY_TOTAL_PREVIOUS))->setResult($launches)->build();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getUpcomingLaunches(Request $request): JsonResponse
    {
        $response = new Response();

        // parameters
        $limit = $request->has("limit") ? (int) $request->get("limit") : Defaults::REQUEST_LIMIT;
        $page = $request->has("page") ? (int) $request->get("page") : Defaults::REQUEST_PAGE;
        $detailed = $request->has("detailed") ? (bool) $request->get("detailed") : Defaults::REQUEST_DETAILED;

        if (!is_bool($detailed)) {
            $detailed = Defaults::REQUEST_DETAILED;
        }

        $launches = $this->launchManager->getLaunches(
            true,
            Defaults::DATABASE_COLUMN_START_NET,
            Defaults::DATABASE_ORDER_ASC,
            $limit,
            $page,
            $detailed
        );

        if ($launches === null) {
            return $response->setStatusCode(204)->build();
        }

        return $response->setTotal($this->launchManager->getTotalAmount(LaunchManager::KEY_TOTAL_UPCOMING))->setResult($launches)->build();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getUnpublishedLaunches(Request $request): JsonResponse
    {
        $response = new Response();

        // parameters
        $limit = $request->has("limit") ? (int) $request->get("limit") : Defaults::REQUEST_LIMIT;
        $page = $request->has("page") ? (int) $request->get("page") : Defaults::REQUEST_PAGE;
        $detailed = $request->has("detailed") ? (bool) $request->get("detailed") : Defaults::REQUEST_DETAILED;

        if (!is_bool($detailed)) {
            $detailed = Defaults::REQUEST_DETAILED;
        }

        $launches = $this->launchManager->getUnpublishedLaunches(
            $limit,
            $page,
            $detailed
        );

        if ($launches === null) {
            return $response->setStatusCode(204)->build();
        }

        return $response->setTotal($this->launchManager->getTotalAmount(LaunchManager::KEY_TOTAL_UNPUBLISHED))->setResult($launches)->build();
    }

    /**
     * @param $provider
     * @param Request $request
     * @return JsonResponse
     */
    public function getLaunchesByProvider($provider, Request $request): JsonResponse
    {
        $response = new Response();

        // provider
        $provider = $this->providerManager->getProviderBySlug($provider);

        if ($provider === null) {
            return $response->setStatusCode(404)->setErrorMessage("This Provider could not be found")->build();
        }

        // parameters
        $limit = $request->has("limit") ? (int) $request->get("limit") : Defaults::REQUEST_LIMIT;
        $page = $request->has("page") ? (int) $request->get("page") : Defaults::REQUEST_PAGE;
        $detailed = $request->has("detailed") ? (bool) $request->get("detailed") : Defaults::REQUEST_DETAILED;

        if (!is_bool($detailed)) {
            $detailed = Defaults::REQUEST_DETAILED;
        }

        $launches = $this->launchManager->getLaunchesByProvider(
            $provider,
            $limit,
            $page,
            $detailed
        );

        if ($launches === null) {
            return $response->setStatusCode(204)->build();
        }

        return $response->setTotal(count($launches))->setResult($launches)->build();
    }

    /**
     * @param $rocket
     * @param Request $request
     * @return JsonResponse
     */
    public function getLaunchesByRocket($rocket, Request $request): JsonResponse
    {
        $response = new Response();

        // rocket
        $rocket = $this->rocketManager->getRocketBySlug($rocket);

        if ($rocket === null) {
            return $response->setStatusCode(404)->setErrorMessage("This Rocket could not be found")->build();
        }

        // parameters
        $limit = $request->has("limit") ? (int) $request->get("limit") : Defaults::REQUEST_LIMIT;
        $page = $request->has("page") ? (int) $request->get("page") : Defaults::REQUEST_PAGE;
        $detailed = $request->has("detailed") ? (bool) $request->get("detailed") : Defaults::REQUEST_DETAILED;

        if (!is_bool($detailed)) {
            $detailed = Defaults::REQUEST_DETAILED;
        }

        $launches = $this->launchManager->getLaunchesByRocket(
            $rocket,
            $limit,
            $page,
            $detailed
        );

        if ($launches === null) {
            return $response->setStatusCode(204)->build();
        }

        return $response->setTotal(count($launches))->setResult($launches)->build();
    }

    /**
     * @param $pad
     * @param Request $request
     * @return JsonResponse
     */
    public function getLaunchesByPad($pad, Request $request): JsonResponse
    {
        $response = new Response();

        // pad
        $pad = $this->padManager->getPadBySlug($pad);

        if ($pad === null) {
            return $response->setStatusCode(404)->setErrorMessage("This Pad could not be found")->build();
        }

        // parameters
        $limit = $request->has("limit") ? (int) $request->get("limit") : Defaults::REQUEST_LIMIT;
        $page = $request->has("page") ? (int) $request->get("page") : Defaults::REQUEST_PAGE;
        $detailed = $request->has("detailed") ? (bool) $request->get("detailed") : Defaults::REQUEST_DETAILED;

        if (!is_bool($detailed)) {
            $detailed = Defaults::REQUEST_DETAILED;
        }

        $launches = $this->launchManager->getLaunchesByPad(
            $pad,
            $limit,
            $page,
            $detailed
        );

        if ($launches === null) {
            return $response->setStatusCode(204)->build();
        }

        return $response->setTotal(count($launches))->setResult($launches)->build();
    }
}
