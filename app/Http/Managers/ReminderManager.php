<?php

declare(strict_types=1);

namespace App\Http\Managers;

use App\Http\Reminders\ReminderTemplateGenerator;
use App\Models\Launch;
use App\Models\LaunchTime;
use App\Models\Pad;
use App\Models\Provider;
use App\Models\Reminder;
use App\Models\Rocket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReminderManager
{

    /** @var LaunchManager */
    private LaunchManager $launchManager;

    /** @var UserManager */
    private UserManager $userManager;

    public const TABLE = 'reminder';
    public const TABLE_X_SUBSCRIPTION = 'reminder_x_subscription';

    private const KEY_USER_ID = 'user_id';
    private const KEY_FOREIGN_ID = 'foreign_id';
    private const KEY_FOREIGN_TYPE = 'foreign_type';

    private const FOREIGN_TYPE_PROVIDER = 'provider';
    private const FOREIGN_TYPE_ROCKET = 'rocket';

    public const SELECT = [
        Reminder::KEY_ID,
        Reminder::KEY_TITLE,
        Reminder::KEY_LAUNCH,
        Reminder::KEY_USER_ID
    ];

    /**
     * ReminderManager constructor.
     */
    public function __construct()
    {
        $this->launchManager = new LaunchManager();
        $this->userManager = new UserManager();
    }

    /**
     * @param string $title
     * @param Launch $launch
     * @param User $user
     * @return Reminder|null
     */
    private function createReminder(string $title, Launch $launch, User $user): ?Reminder
    {
        try {
            $id = DB::table(self::TABLE)
                ->insertGetId([
                    Reminder::KEY_TITLE => $title,
                    Reminder::KEY_LAUNCH => json_encode([$launch->getSlug()], JSON_THROW_ON_ERROR),
                    Reminder::KEY_USER_ID => $user->getId()
                ]);

            return $this->getReminderById($id);
        } catch (\JsonException $exception) {
            return null;
        }
    }

    /**
     * @param Reminder $reminder
     * @return Reminder|null
     */
    public function executeReminder(Reminder $reminder): ?Reminder
    {
        ReminderTemplateGenerator::generate($reminder);
        return $this->createReminder($reminder->getTitle(), $reminder->getLaunch(), $reminder->getUser());
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
            ->join(RocketManager::TABLE, RocketManager::TABLE . "." . Rocket::KEY_ID, "=", LaunchManager::TABLE . "." . Launch::KEY_ROCKET_ID)
            ->join(ProviderManager::TABLE, ProviderManager::TABLE . "." . Provider::KEY_ID, "=", LaunchManager::TABLE . "." . Launch::KEY_PROVIDER_ID)
            ->join(PadManager::TABLE, PadManager::TABLE . "." . Pad::KEY_ID, "=", LaunchManager::TABLE . "." . Launch::KEY_PAD_ID)
            ->whereDate(LaunchTime::KEY_LAUNCH_NET, Carbon::now()->format("Y-m-d"))
            ->where(Launch::KEY_PUBLISHED, "=", 1)
            ->get();

        foreach ($result as $item) {
            $launches[] = $this->launchManager->buildLaunchFromDatabaseResult($item, true);
        }

        return $launches;
    }

    /**
     * @param int $id
     * @return Reminder|null
     */
    private function getReminderById(int $id): ?Reminder
    {
        $result = DB::table(self::TABLE)
            ->select(self::SELECT)
            ->where(Reminder::KEY_ID, '=', $id)
            ->first();

        if ($result === null) {
            return null;
        }

        return $this->buildReminderFromDatabaseResult($result);
    }

    /**
     * @param User $user
     * @param Launch $launch
     * @return bool
     */
    public function hasSubscribed(User $user, Launch $launch): bool
    {
        if ($user === null || ($launch->getProvider() === null && $launch->getRocket() === null)) {
            return false;
        }

        $provider = DB::table(self::TABLE_X_SUBSCRIPTION)
            ->where([
                self::KEY_USER_ID => $user->getId(),
                self::KEY_FOREIGN_ID => $launch->getProvider() === null ? null : $launch->getProvider()->getId(),
                self::KEY_FOREIGN_TYPE => self::FOREIGN_TYPE_PROVIDER
            ])
            ->first();

        $rocket = DB::table(self::TABLE_X_SUBSCRIPTION)
            ->where([
                self::KEY_USER_ID => $user->getId(),
                self::KEY_FOREIGN_ID => $launch->getRocket() === null ? null : $launch->getRocket()->getId(),
                self::KEY_FOREIGN_TYPE => self::FOREIGN_TYPE_ROCKET
            ])
            ->first();

        return !($rocket === null && $provider === null);
    }

    /**
     * @param $result
     * @return Reminder
     */
    private function buildReminderFromDatabaseResult($result): Reminder
    {
        $reminder = new Reminder();

        if (isset($result->id)) {
            $reminder->setId($result->id);
        }

        if (isset($result->title)) {
            $reminder->setTitle($result->id);
        }

        if (isset($result->launch)) {
            $reminder->setLaunch($this->launchManager->getLaunchBySlug($result->launch));
        }

        if (isset($result->user_id)) {
            $reminder->setUser($this->userManager->getUserById($result->user_id));
        }

        return $reminder;
    }
}
