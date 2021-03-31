<?php

declare(strict_types=1);

namespace App\Http\Managers;

use App\Models\Launch;
use App\Models\LaunchStatus;
use App\Models\LaunchTime;
use App\Models\Pad;
use App\Models\Provider;
use App\Models\Rocket;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use function PHPUnit\Framework\isEmpty;

class LaunchManager
{

    public const TABLE = "rl_launch";

    private const SELECT = [
        "id", "name", "slug", "description", "statusId", "rocketId", "padId", "providerId", "tags", "livestreamURL", "startWinOpen", "startWinClose", "startNet", "published"
    ];

    public const KEY_TOTAL_DEFAULT = "default";
    public const KEY_TOTAL_UPCOMING = "upcoming";
    public const KEY_TOTAL_PREVIOUS = "previous";
    public const KEY_TOTAL_UNPUBLISHED = "unpublished";

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
     * LaunchManager constructor.
     */
    public function __construct()
    {
        $this->rocketManager = new RocketManager();
        $this->providerManager = new ProviderManager();
        $this->padManager = new PadManager();
        $this->statusManager = new StatusManager();
    }

    /**
     * @param int|string $id
     * @return Launch|null
     */
    public function getLaunchById($id): ?Launch
    {
        $result = DB::table(self::TABLE)
            ->select(self::SELECT)
            ->where("id", "=", $id)
            ->first();

        if ($result === null) {
            return null;
        }

        return $this->buildLaunchFromDatabaseResult($result);
    }

    /**
     * @param string $slug
     * @param bool $detailed
     * @return Launch|null
     */
    public function getLaunchBySlug(string $slug, bool $detailed = Defaults::REQUEST_DETAILED): ?Launch
    {
        $result = DB::table(self::TABLE)
            ->select(self::SELECT)
            ->where("slug", "=", $slug)
            ->first();

        if ($result === null) {
            return null;
        }

        return $this->buildLaunchFromDatabaseResult($result, $detailed);
    }

    /**
     * @param bool $upcoming
     * @param string $orderBy
     * @param string $orderMethod
     * @param int $limit
     * @param int $page
     * @param bool $detailed
     * @return array
     */
    public function getLaunches(bool $upcoming, string $orderBy, string $orderMethod, int $limit, int $page, bool $detailed): array
    {
        if ($limit > Defaults::REQUEST_LIMIT_MAX) {
            $limit = Defaults::REQUEST_LIMIT_MAX;
        }

        $currentTime = Carbon::now()->toDateTimeString();
        $result = DB::table(self::TABLE)
            ->select(self::SELECT)
            ->offset(($page - 1) * $limit)
            ->limit($limit)
            ->orderBy($orderBy, $orderMethod)
            ->where(Defaults::DATABASE_COLUMN_START_NET, ($upcoming ? '>' : '<'), $currentTime)
            ->where("published", "=", 1)
            ->get();

        return $this->extractLaunches($result, $detailed);
    }

    /**
     * @param int $limit
     * @param int $page
     * @param bool $detailed
     * @return array
     */
    public function getUnpublishedLaunches(int $limit, int $page, bool $detailed): array
    {
        if ($limit > Defaults::REQUEST_LIMIT_MAX) {
            $limit = Defaults::REQUEST_LIMIT_MAX;
        }

        $result = DB::table(self::TABLE)
            ->select(self::SELECT)
            ->offset(($page - 1) * $limit)
            ->limit($limit)
            ->where("published", "=", 0)
            ->get();

        return $this->extractLaunches($result, $detailed);
    }

    /**
     * @param int $limit
     * @param int $page
     * @param bool $detailed
     * @return array
     */
    public function getLaunchesAdmin(int $limit, int $page, bool $detailed): array
    {
        if ($limit > Defaults::REQUEST_LIMIT_MAX) {
            $limit = Defaults::REQUEST_LIMIT_MAX;
        }

        $result = DB::table(self::TABLE)
            ->select(self::SELECT)
            ->offset(($page - 1) * $limit)
            ->limit($limit)
            ->get();

        return $this->extractLaunches($result, $detailed);
    }

    /**
     * @param Provider $provider
     * @param int $page
     * @param int $limit
     * @param bool $detailed
     * @return array
     */
    public function getLaunchesByProvider(Provider $provider, int $limit, int $page, bool $detailed): array
    {
        $result = DB::table(self::TABLE)
            ->select(self::SELECT)
            ->offset(($page - 1) * $limit)
            ->limit($limit)
            ->where("providerId", "=", $provider->getId())
            ->where("published", "=", 1)
            ->get();

        return $this->extractLaunches($result, $detailed);
    }

    /**
     * @param Rocket $rocket
     * @param int $page
     * @param int $limit
     * @param bool $detailed
     * @return array
     */
    public function getLaunchesByRocket(Rocket $rocket, int $limit, int $page, bool $detailed): array
    {
        $result = DB::table(self::TABLE)
            ->select(self::SELECT)
            ->offset(($page - 1) * $limit)
            ->limit($limit)
            ->where("rocketId", "=", $rocket->getId())
            ->where("published", "=", 1)
            ->get();

        return $this->extractLaunches($result, $detailed);
    }

    /**
     * @param Pad $pad
     * @param int $page
     * @param int $limit
     * @param bool $detailed
     * @return array
     */
    public function getLaunchesByPad(Pad $pad, int $limit, int $page, bool $detailed): array
    {
        $result = DB::table(self::TABLE)
            ->select(self::SELECT)
            ->where("padId", "=", $pad->getId())
            ->where("published", "=", 1)
            ->offset(($page - 1) * $limit)
            ->limit($limit)
            ->get();

        return $this->extractLaunches($result, $detailed);
    }

    /**
     * @param $slug
     */
    public function deleteLaunch($slug): void
    {
        DB::table(self::TABLE)->where("slug", "=", $slug)->delete();
    }

    /**
     * @param string $key
     * @return int|string
     */
    public function getTotalAmount(string $key = self::KEY_TOTAL_DEFAULT)
    {
        switch ($key) {
            case self::KEY_TOTAL_UNPUBLISHED:
                return DB::table(self::TABLE)->selectRaw("COUNT(*) as total")->where("published", "=", 0)->first()->total ?? 0;
            case self::KEY_TOTAL_UPCOMING:
                return DB::table(self::TABLE)->selectRaw("COUNT(*) as total")->where("published", "=", 1)
                        ->where(Defaults::DATABASE_COLUMN_START_NET, '>', Carbon::now()->toDateTimeString())->first()->total ?? 0;
            case self::KEY_TOTAL_PREVIOUS:
                return DB::table(self::TABLE)->selectRaw("COUNT(*) as total")->where("published", "=", 1)
                        ->where(Defaults::DATABASE_COLUMN_START_NET, '<', Carbon::now()->toDateTimeString())->first()->total ?? 0;
            case self::KEY_TOTAL_DEFAULT:
            default:
                return DB::table(self::TABLE)->selectRaw("COUNT(*) as total")->where("published", "=", 1)->first()->total ?? 0;
        }
    }

    /**
     * @param Collection $result
     * @param bool $detailed
     * @return array
     */
    private function extractLaunches(Collection $result, bool $detailed): array
    {
        $launches = [];

        foreach ($result as $launch)
        {
            $launches[] = $this->buildLaunchFromDatabaseResult($launch, $detailed);
        }

        return $launches;
    }

    /**
     * @param $result
     * @param bool $detailed
     * @return Launch
     */
    private function buildLaunchFromDatabaseResult($result, bool $detailed = Defaults::REQUEST_DETAILED): Launch
    {
        $launch = new Launch();

        if (isset($result->id)) {
            $launch->setId($result->id);
        }

        if (isset($result->name)) {
            $launch->setName($result->name);
        }

        if (isset($result->slug)) {
            $launch->setSlug($result->slug);
        }

        if (isset($result->description)) {
            $launch->setDescription($result->description);
        }

        if (isset($result->tags)) {
            try {
                $launch->setTags(json_decode($result->tags, true, 512, JSON_THROW_ON_ERROR));
            } catch (\JsonException $ignored) { }
        }

        if (
            $detailed
            && isset($result->rocketId)
        ) {
            $launch->setRocket($this->rocketManager->getRocketById($result->rocketId));
        }

        if (
            $detailed
            && isset($result->providerId)
        ) {
            $launch->setProvider($this->providerManager->getProviderById($result->providerId));
        }

        if (
            $detailed
            && isset($result->statusId)
        ) {
            $launch->setStatus($this->statusManager->getStatusById($result->statusId));
        }

        if (
            $detailed
            && isset($result->padId)
        ) {
            $launch->setPad($this->padManager->getPadById($result->padId));
        }

        if (
            $detailed
            && isset($result->startWinOpen, $result->startWinClose, $result->startNet)
            && $result->startWinOpen !== null
            && $result->startWinClose !== null
            && $result->startNet !== null
        ) {
            $launchTime = new LaunchTime();

            $launchTime->setLaunchWinOpen($this->toDateTime($result->startWinOpen));
            $launchTime->setLaunchWinClose($this->toDateTime($result->startWinClose));
            $launchTime->setLaunchNet($this->toDateTime($result->startNet));

            $launch->setLaunchTime($launchTime);
        }

        $launch->setDetailed($detailed);
        $launch->setPublished(isset($result->published) ? (bool)$result->published : false);

        return $launch;
    }

    private function toDateTime(string $timeString): DateTime {
        return DateTime::createFromFormat("Y-m-d H:i:s", $timeString);
    }

    /**
     * @param string $name
     * @param string|null $description
     * @param Rocket|null $rocket
     * @param Pad|null $pad
     * @param Provider|null $provider
     * @param LaunchStatus|null $launchStatus
     * @param LaunchTime|null $launchTime
     * @param array $tags
     * @param string|null $livestreamURL
     * @return bool
     * @throws \JsonException
     */
    public function createLaunch(
        string $name,
        ?string $description,
        ?Rocket $rocket,
        ?Pad $pad,
        ?Provider $provider,
        ?LaunchStatus $launchStatus,
        ?LaunchTime $launchTime,
        array $tags,
        ?string $livestreamURL
    ): bool {
        $launch = $this->getLaunchBySlug(Utils::stringToSlug($name));

        if ($launch !== null) {
            return false;
        }

        return DB::table(self::TABLE)->insert([
            "name" => $name,
            "slug" => Utils::stringToSlug($name),
            "description" => $description,
            "rocketId" => $rocket === null ? null : $rocket->getId(),
            "providerId" => $provider === null ? null : $provider->getId(),
            "padId" => $pad === null ? null : $pad->getId(),
            "statusId" => $launchStatus === null ? null : $launchStatus->getId(),
            "tags" => json_encode($tags, JSON_THROW_ON_ERROR),
            "livestreamURL" => $livestreamURL,
            "startNet" => $launchTime === null ? null : $launchTime->getLaunchNet()->format("Y-m-d H:i:s"),
            "startWinOpen" => $launchTime === null ? null : $launchTime->getLaunchWinOpen()->format("Y-m-d H:i:s"),
            "startWinClose" => $launchTime === null ? null : $launchTime->getLaunchWinClose()->format("Y-m-d H:i:s"),
            "published" => false
        ]);
    }

    /**
     * @param string $slug
     * @param string|null $name
     * @param string|null $description
     * @param Rocket|null $rocket
     * @param Pad|null $pad
     * @param Provider|null $provider
     * @param LaunchStatus|null $launchStatus
     * @param LaunchTime|null $launchTime
     * @param array|null $tags
     * @param string|null $livestreamURL
     * @param bool|null $published
     * @return bool
     */
    public function updateLaunch(
        string $slug,
        ?string $name,
        ?string $description,
        ?Rocket $rocket,
        ?Pad $pad,
        ?Provider $provider,
        ?LaunchStatus $launchStatus,
        ?LaunchTime $launchTime,
        ?array $tags,
        ?string $livestreamURL,
        ?bool $published
    ): bool {
        $launch = $this->getLaunchBySlug($slug);

        if ($launch === null) {
            return false;
        }

        DB::table(self::TABLE)
            ->where("slug", "=", $slug)
            ->update(
                $this->buildUpdateArray(
                    $name,
                    $description,
                    $rocket,
                    $pad,
                    $provider,
                    $launchStatus,
                    $launchTime,
                    $tags,
                    $livestreamURL,
                    $published
                )
            );
        return true;
    }

    /**
     * @param string|null $name
     * @param string|null $description
     * @param Rocket|null $rocket
     * @param Pad|null $pad
     * @param Provider|null $provider
     * @param LaunchStatus|null $launchStatus
     * @param LaunchTime|null $launchTime
     * @param array|null $tags
     * @param string|null $livestreamURL
     * @param bool|null $published
     * @return array
     */
    private function buildUpdateArray(
        ?string $name,
        ?string $description,
        ?Rocket $rocket,
        ?Pad $pad,
        ?Provider $provider,
        ?LaunchStatus $launchStatus,
        ?LaunchTime $launchTime,
        ?array $tags,
        ?string $livestreamURL,
        ?bool $published
    ): array {
        $array = [];

        if ($name !== null) {
            $array["name"] = $name;
        }

        if ($description !== null) {
            $array["description"] = $description;
        }

        if ($rocket !== null) {
            $array["rocketId"] = $rocket->getId();
        }

        if ($pad !== null) {
            $array["padId"] = $pad->getId();
        }

        if ($provider !== null) {
            $array["providerId"] = $provider->getId();
        }

        if ($launchStatus !== null) {
            $array["statusId"] = $launchStatus->getId();
        }

        if ($launchTime !== null) {
            $array["startNet"] = $launchTime->getLaunchNet()->format("Y-m-d H:i:s");
            $array["startWinOpen"] = $launchTime->getLaunchWinOpen()->format("Y-m-d H:i:s");
            $array["startWinClose"] = $launchTime->getLaunchWinClose()->format("Y-m-d H:i:s");
        }

        if (!isEmpty($tags)) {
            try {
                $array["tags"] = json_encode($tags, JSON_THROW_ON_ERROR);
            } catch (\JsonException $ignored) { }
        }

        if ($livestreamURL !== null) {
            $array["livestreamURL"] = $livestreamURL;
        }

        if ($published !== null) {
            $array["published"] = $published;
        }

        return $array;
    }
}
