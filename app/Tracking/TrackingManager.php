<?php

declare(strict_types=1);

namespace App\Tracking;

use App\Http\Managers\Utils;
use App\Http\Response\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TrackingManager
{

    /*
     *
     * SQL QUERY IDEAS FOR TRACKING DATA
     *
     *  1. Success Rate per Route
     *  SELECT `request_method`, SUM(`request_success`) / COUNT(*) * 100 AS success_rate FROM `tracking_data` WHERE `request_status_code` NOT LIKE 'pending' AND `request_status_code` NOT LIKE '401' GROUP BY `request_method`
     *
     *  2. Detailed vs Undetailed Requests
     *  SELECT SUM(`request_detailed`) / COUNT(*) * 100 AS detailed_rate FROM `tracking_data`
     *
     *  3. Who Requests so often?
     *  SELECT `client_ip`, COUNT(*) AS count FROM `tracking_data` GROUP BY `client_ip`
     *  (possible to set date range, if traffic is high)
     */

    /** @var string */
    private const STATUS_PENDING = 'pending';

    /** @var string */
    private const TABLE = 'tracking_data';

    /**
     * @param Request $request
     */
    public function handle(Request $request): void
    {
        $trackingId = Utils::generateString();
        $requestPath = $request->getPathInfo();
        $requestMethod = $request->route()[1]['as'];
        $requestMethodType = $request->method();
        $clientIp = $request->getClientIp();
        $requestSuccess = 0;
        $requestStatusCode = self::STATUS_PENDING;

        $request->attributes->set('tracking_id', $trackingId);

        DB::table(self::TABLE)
            ->insert([
                'tracking_id' => $trackingId,
                'request_path' => $requestPath,
                'request_method' => $requestMethod,
                'request_method_type' => $requestMethodType,
                'request_success' => $requestSuccess,
                'request_status_code' => $requestStatusCode,
                'client_ip' => $clientIp,
            ]);
    }

    /**
     * @param string $requestId
     * @param Response $response
     * @param null $detailed
     */
    public function update(string $requestId, Response $response, $detailed = null): void
    {
        $requestSuccess = $response->isSuccessful();
        $requestStatusCode = $response->getStatusCode();

        DB::table(self::TABLE)
            ->where('tracking_id', '=', $requestId)
            ->update([
                'request_success' => $requestSuccess,
                'request_status_code' => $requestStatusCode,
                'request_detailed' => $detailed,
                'request_execution_time' => $response->getExecutionTime(),
            ]);
    }
}
