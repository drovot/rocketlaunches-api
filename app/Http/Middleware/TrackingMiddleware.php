<?php

namespace App\Http\Middleware;

use App\Http\Response\Response;
use App\Tracking\TrackingManager;
use Closure;
use Illuminate\Http\Request;

class TrackingMiddleware
{

    /** @var TrackingManager */
    private TrackingManager $trackingManager;

    /**
     * TrackingMiddleware constructor.
     */
    public function __construct()
    {
        $this->trackingManager = new TrackingManager();
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
        $this->trackingManager->handle($request);
        return $next($request);
    }
}
