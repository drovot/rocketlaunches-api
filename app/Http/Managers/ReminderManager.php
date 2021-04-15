<?php

declare(strict_types=1);

namespace App\Http\Managers;

use App\Http\Reminders\ReminderTemplateGenerator;
use App\Models\Launch;
use App\Models\Reminder;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReminderManager
{

    /** @var LaunchManager */
    private LaunchManager $launchManager;

    private const REMINDER_TABLE = 'reminder';

    /**
     * ReminderManager constructor.
     */
    public function __construct()
    {
        $this->launchManager = new LaunchManager();
    }

    /**
     * @param Reminder $reminder
     * @return bool
     */
    public function executeReminder(Reminder $reminder): bool
    {
        ReminderTemplateGenerator::generate($reminder);

        try {
            DB::table(self::REMINDER_TABLE)
                ->insert([
                    "title" => $reminder->getTitle(),
                    "launches" => json_encode([$reminder->getLaunch()->getSlug()], JSON_THROW_ON_ERROR),
                    "user_id" => $reminder->getUser()->getId()
                ]);
        } catch (\JsonException $exception) {
            return false;
        }

        return true;
    }

    /**
     * @return User[]|array
     */
    public function getAllNotifiableUsers(): array
    {
        $users = [];
        $result = DB::table("user")
            ->select([
                "user.id as id",
                "user.firstname as firstname",
                "user.lastname as lastname",
                "user.email as email"
            ])
            ->leftJoin("reminder_x_settings", "reminder_x_settings.user_id", "user.id")
            ->where("reminder_x_settings.receive_notifications", "=", 1)
            ->get();

        foreach ($result as $item) {
            $user = new User();
            $user->setId((int) $item->id);
            $user->setFirstname($item->firstname);
            $user->setLastname($item->lastname);
            $user->setEmail($item->email);

            $users[] = $user;
        }

        return $users;
    }


    /**
     * @return Launch[]|array
     */
    public function getDailyLaunches(): array
    {
        $launches = [];
        $result = DB::table(LaunchManager::TABLE)
            ->select(LaunchManager::SELECT)
            ->join("rl_rocket", "rl_rocket.id", "=", "rl_launch.rocket_id")
            ->join("rl_provider", "rl_provider.id", "=", "rl_launch.provider_id")
            ->join("rl_pad", "rl_pad.id", "=", "rl_launch.pad_id")
            ->whereDate("start_net", Carbon::now()->format("Y-m-d"))
            ->where("published", "=", 1)
            ->get();

        foreach ($result as $item) {
            $launches[] = $this->launchManager->buildLaunchFromDatabaseResult($item, true);
        }

        return $launches;
    }
}
