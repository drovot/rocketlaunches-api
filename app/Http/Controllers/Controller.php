<?php

namespace App\Http\Controllers;

use App\Http\Response\Response;
use Illuminate\Http\JsonResponse;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{

    /**
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $response = new Response();

        $response->setResult([
            "name" => env("APP_NAME"),
            "author" => env("APP_AUTHOR"),
            "version" => env("APP_VERSION"),
            "baseURL" => env("APP_URL"),
        ]);

        return $response->build();
    }
}
