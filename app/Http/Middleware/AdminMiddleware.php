<?php

namespace App\Http\Middleware;

use App\Http\Response\Response;
use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {

        if (!$request->hasHeader("Authorization") || !$this->checkAdminPassword($request->header("Authorization"))) {
            $response = new Response();

            if ($request->attributes->has('tracking_id')) {
                $response->setTrackingId($request->attributes->get('tracking_id'));
            }

            return $response->setStatusCode(401)->build();
        }

        return $next($request);
    }

    /**
     * @param string $password
     * @return bool
     */
    private function checkAdminPassword(string $password): bool
    {
        $check = env('SPACE_ADMIN_PASSWORD');
        $passwordExplode = explode(" ", $password);
        $password = $passwordExplode[1] ?? "";

        if ($check === null || $password === "") {
            return false;
        }

        return password_verify($password, (password_hash($check, PASSWORD_BCRYPT)));
    }
}
