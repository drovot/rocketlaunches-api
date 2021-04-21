<?php

namespace App\Http\Middleware;

use App\Http\Managers\UserManager;
use App\Http\Response\Response;
use Illuminate\Http\Request;
use Closure;

class AuthenticationMiddleware
{

    /** @var UserManager */
    private UserManager $userManager;

    /**
     * AuthenticationMiddleware constructor.
     */
    public function __construct()
    {
        $this->userManager = new UserManager();
    }


    /**
     * Track incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $this->userManager->getUserByApiToken($request->header('Authorization'));

        if ($user === null) {
            $response = new Response();
            return $response->setStatusCode(401)->build();
        }

        // add user to attributes
        $request->attributes->set('user', $user);

        return $next($request);
    }
}
