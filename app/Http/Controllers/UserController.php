<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Managers\UserManager;
use App\Http\Response\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;

class UserController extends BaseController
{

    /**
     * @var UserManager
     */
    private UserManager $userManager;

    /**
     * UserController constructor.
     */
    public function __construct()
    {
        $this->userManager = new UserManager();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getUser(Request $request): JsonResponse
    {
        $response = new Response();

        $trackingId = $request->attributes->has('tracking_id') ? $request->attributes->get('tracking_id') : null;
        $response->setTrackingId($trackingId);

        $user = $this->userManager->getUserByApiToken($request->attributes->api_token);

        if ($user === null) {
            return $response->setStatusCode(404)->build();
        }

        return $response
            ->setResult($user)
            ->build();
    }
}
