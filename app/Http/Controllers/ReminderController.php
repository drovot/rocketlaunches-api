<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Managers\ReminderManager;
use App\Http\Response\Response;
use App\Models\Reminder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;

class ReminderController extends BaseController
{

    private const REMINDER_TITLE_SINGLE = '%s will launch today!';

    private const STATUS_CODE_NO_LAUNCHES = 240;
    private const STATUS_NO_LAUNCHES = 'OK, nothing found';

    /** @var ReminderManager  */
    private ReminderManager $reminderManager;

    /**
     * ReminderController constructor.
     */
    public function __construct()
    {
        $this->reminderManager = new ReminderManager();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function sendReminder(Request $request): JsonResponse
    {
        $response = new Response();

        $launches = $this->reminderManager->getDailyLaunches();
        $users = $this->reminderManager->getAllNotifiableUsers();

        $trackingId = $request->attributes->has('tracking_id') ? $request->attributes->get('tracking_id') : null;
        $response->setTrackingId($trackingId);

        if (empty($launches)) {
            return $response
                ->setStatusCode(self::STATUS_CODE_NO_LAUNCHES, self::STATUS_NO_LAUNCHES)
                ->setErrorMessage(self::STATUS_NO_LAUNCHES)
                ->build();
        }

        // loop through each launch and send multiple mails
        foreach ($users as $user) {
            foreach ($launches as $launch) {
                if (!$this->reminderManager->hasSubscribed($user, $launch)) {
                    continue;
                }

                $reminder = new Reminder();
                $reminder->setTitle(sprintf(self::REMINDER_TITLE_SINGLE, $launch->getName()));
                $reminder->setLaunch($launch);
                $reminder->setUser($user);

                $this->reminderManager->executeReminder($reminder);
            }
        }

        return $response->build();
    }
}
