<?php

declare(strict_types=1);

namespace App\Http\Managers;

use App\Models\Launch;
use App\Models\Status;
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

    public const TABLE = 'rl_launch';

    public const SELECT = [
        self::TABLE . ".id as id",
        self::TABLE . ".name as name",
        self::TABLE . ".slug as slug",
        self::TABLE . ".description as description",
        self::TABLE . ".tags as tags",
        self::TABLE . ".livestream_url as livestream_url",
        self::TABLE . ".start_win_open as start_win_open",
        self::TABLE . ".start_win_close as start_win_close",
        self::TABLE . ".start_net as start_net",
        self::TABLE . ".published as published",
        self::TABLE . ".status_id as status_id",
        RocketManager::TABLE . ".id as rocket_id",
        RocketManager::TABLE . ".name as rocket_name",
        RocketManager::TABLE . ".slug as rocket_slug",
        RocketManager::TABLE . ".wiki_url as rocket_wiki_url",
        RocketManager::TABLE . ".image_url as rocket_image_url",
        ProviderManager::TABLE . ".id as provider_id",
        ProviderManager::TABLE . ".name as provider_name",
        ProviderManager::TABLE . ".slug as provider_slug",
        ProviderManager::TABLE . ".abbreviation as provider_abbreviation",
        ProviderManager::TABLE . ".website_url as provider_website_url",
        ProviderManager::TABLE . ".wiki_url as provider_wiki_url",
        ProviderManager::TABLE . ".logo_url as provider_logo_url",
        ProviderManager::TABLE . ".image_url as provider_image_url",
        PadManager::TABLE . ".id as pad_id",
        PadManager::TABLE . ".location_id as pad_location_id",
        PadManager::TABLE . ".name as pad_name",
        PadManager::TABLE . ".slug as pad_slug",
        PadManager::TABLE . ".wiki_url as pad_wiki_url",
        PadManager::TABLE . ".image_url as pad_image_url",
    ];

    public const KEY_TOTAL_DEFAULT = "default";
    public const KEY_TOTAL_ALL = "all";
    public const KEY_TOTAL_UPCOMING = "upcoming";
    public const KEY_TOTAL_PREVIOUS = "previous";
    public const KEY_TOTAL_UNPUBLISHED = "unpublished";

    /**
     * @var StatusManager
     */
    private StatusManager $statusManager;

    /**
     * @var LocationManager
     */
    private LocationManager $locationManager;

    /**
     * LaunchManager constructor.
     */
    public function __construct()
    {
        $this->statusManager = new StatusManager();
        $this->locationManager = new LocationManager();
    }

    /**
     * @param int|string $id
     * @return Launch|null
     */
    public function getLaunchById($id): ?Launch
    {
        $result = DB::table(self::TABLE)
            ->select(self::SELECT)
            ->join(RocketManager::TABLE, RocketManager::TABLE . "." . Rocket::KEY_ID, "=", self::TABLE . "." . Launch::KEY_ROCKET_ID)
            ->join(ProviderManager::TABLE, ProviderManager::TABLE . "." . Provider::KEY_ID, "=", self::TABLE . "." . Launch::KEY_PROVIDER_ID)
            ->join(PadManager::TABLE, PadManager::TABLE . "." . Pad::KEY_ID, "=", self::TABLE . "." . Launch::KEY_PAD_ID)
            ->where(self::TABLE . '.' . Launch::KEY_ID, "=", $id)
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
            ->join(RocketManager::TABLE, RocketManager::TABLE . "." . Rocket::KEY_ID, "=", self::TABLE . "." . Launch::KEY_ROCKET_ID)
            ->join(ProviderManager::TABLE, ProviderManager::TABLE . "." . Provider::KEY_ID, "=", self::TABLE . "." . Launch::KEY_PROVIDER_ID)
            ->join(PadManager::TABLE, PadManager::TABLE . "." . Pad::KEY_ID, "=", self::TABLE . "." . Launch::KEY_PAD_ID)
            ->where(self::TABLE . '.' . Launch::KEY_ID, "=", $id)
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
            ->join(RocketManager::TABLE, RocketManager::TABLE . '.' . Rocket::KEY_ID, '=', self::TABLE . '.' . Launch::KEY_ROCKET_ID)
            ->join(ProviderManager::TABLE, ProviderManager::TABLE . '.' . Provider::KEY_ID, '=', self::TABLE . '.' . Launch::KEY_PROVIDER_ID)
            ->join(PadManager::TABLE, PadManager::TABLE . '.' . Pad::KEY_ID, '=', self::TABLE . '.' . Launch::KEY_PAD_ID)
            ->offset(($page - 1) * $limit)
            ->limit($limit)
            ->orderBy($orderBy, $orderMethod)
            ->where(Defaults::DATABASE_COLUMN_START_NET, ($upcoming ? '>' : '<'), $currentTime)
            ->where(Launch::KEY_PUBLISHED, "=", 1)
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
            ->join(RocketManager::TABLE, RocketManager::TABLE . "." . Rocket::KEY_ID, "=", self::TABLE . "." . Launch::KEY_ROCKET_ID)
            ->join(ProviderManager::TABLE, ProviderManager::TABLE . "." . Provider::KEY_ID, "=", self::TABLE . "." . Launch::KEY_PROVIDER_ID)
            ->join(PadManager::TABLE, PadManager::TABLE . "." . Pad::KEY_ID, "=", self::TABLE . "." . Launch::KEY_PAD_ID)
            ->offset(($page - 1) * $limit)
            ->limit($limit)
            ->where(Launch::KEY_PUBLISHED, "=", 0)
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
            ->join(RocketManager::TABLE, RocketManager::TABLE . "." . Rocket::KEY_ID, "=", self::TABLE . "." . Launch::KEY_ROCKET_ID)
            ->join(ProviderManager::TABLE, ProviderManager::TABLE . "." . Provider::KEY_ID, "=", self::TABLE . "." . Launch::KEY_PROVIDER_ID)
            ->join(PadManager::TABLE, PadManager::TABLE . "." . Pad::KEY_ID, "=", self::TABLE . "." . Launch::KEY_PAD_ID)
            ->offset(($page - 1) * $limit)
            ->orderBy("published", "DESC")
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
            ->join(RocketManager::TABLE, RocketManager::TABLE . "." . Rocket::KEY_ID, "=", self::TABLE . "." . Launch::KEY_ROCKET_ID)
            ->join(ProviderManager::TABLE, ProviderManager::TABLE . "." . Provider::KEY_ID, "=", self::TABLE . "." . Launch::KEY_PROVIDER_ID)
            ->join(PadManager::TABLE, PadManager::TABLE . "." . Pad::KEY_ID, "=", self::TABLE . "." . Launch::KEY_PAD_ID)
            ->offset(($page - 1) * $limit)
            ->limit($limit)
            ->where(Launch::KEY_PROVIDER_ID, "=", $provider->getId())
            ->where(Launch::KEY_PUBLISHED, "=", 1)
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
            ->join(RocketManager::TABLE, RocketManager::TABLE . "." . Rocket::KEY_ID, "=", self::TABLE . "." . Launch::KEY_ROCKET_ID)
            ->join(ProviderManager::TABLE, ProviderManager::TABLE . "." . Provider::KEY_ID, "=", self::TABLE . "." . Launch::KEY_PROVIDER_ID)
            ->join(PadManager::TABLE, PadManager::TABLE . "." . Pad::KEY_ID, "=", self::TABLE . "." . Launch::KEY_PAD_ID)
            ->offset(($page - 1) * $limit)
            ->limit($limit)
            ->where(Launch::KEY_ROCKET_ID, "=", $rocket->getId())
            ->where(Launch::KEY_PUBLISHED, "=", 1)
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
            ->join(RocketManager::TABLE, RocketManager::TABLE . "." . Rocket::KEY_ID, "=", self::TABLE . "." . Launch::KEY_ROCKET_ID)
            ->join(ProviderManager::TABLE, ProviderManager::TABLE . "." . Provider::KEY_ID, "=", self::TABLE . "." . Launch::KEY_PROVIDER_ID)
            ->join(PadManager::TABLE, PadManager::TABLE . "." . Pad::KEY_ID, "=", self::TABLE . "." . Launch::KEY_PAD_ID)
            ->where(Launch::KEY_PAD_ID, "=", $pad->getId())
            ->where(Launch::KEY_PUBLISHED, "=", 1)
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
        DB::table(self::TABLE)->where(Launch::KEY_SLUG, "=", $slug)->delete();
    }

    /**
     * @param string $key
     * @return int|string
     */
    public function getTotalAmount(string $key = self::KEY_TOTAL_DEFAULT)
    {
        switch ($key) {
            case self::KEY_TOTAL_UNPUBLISHED:
                return DB::table(self::TABLE)->selectRaw("COUNT(*) as total")
                        ->where(Launch::KEY_PUBLISHED, "=", 0)->first()->total ?? 0;
            case self::KEY_TOTAL_UPCOMING:
                return DB::table(self::TABLE)->selectRaw("COUNT(*) as total")->where(Launch::KEY_PUBLISHED, "=", 1)
                        ->where(Defaults::DATABASE_COLUMN_START_NET, '>', Carbon::now()->toDateTimeString())->first()->total ?? 0;
            case self::KEY_TOTAL_PREVIOUS:
                return DB::table(self::TABLE)->selectRaw("COUNT(*) as total")->where(Launch::KEY_PUBLISHED, "=", 1)
                        ->where(Defaults::DATABASE_COLUMN_START_NET, '<', Carbon::now()->toDateTimeString())->first()->total ?? 0;
            case self::KEY_TOTAL_ALL:
                return DB::table(self::TABLE)->selectRaw("COUNT(*) as total")->first()->total ?? 0;
            case self::KEY_TOTAL_DEFAULT:
            default:
                return DB::table(self::TABLE)->selectRaw("COUNT(*) as total")
                        ->where(Launch::KEY_PUBLISHED, "=", 1)->first()->total ?? 0;
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
    public function buildLaunchFromDatabaseResult($result, bool $detailed = Defaults::REQUEST_DETAILED): Launch
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
            && isset($result->rocket_slug)
        ) {
            $rocket = new Rocket();
            $rocket->setSlug($result->rocket_slug);

            if (isset($result->rocket_id)) {
                $rocket->setId($result->rocket_id);
            }

            if (isset($result->rocket_name)) {
                $rocket->setName($result->rocket_name);
            }

            if (isset($result->rocket_wiki_url)) {
                $rocket->setWikiURL($result->rocket_wiki_url);
            }

            if (isset($result->rocket_image_url)) {
                $rocket->setImageURL($result->rocket_image_url);
            }

            $launch->setRocket($rocket);
        }

        if (
            $detailed
            && isset($result->provider_slug)
        ) {
            $provider = new Provider();
            $provider->setSlug($result->provider_slug);

            if (isset($result->provider_id)) {
                $provider->setId($result->provider_id);
            }

            if (isset($result->provider_name)) {
                $provider->setName($result->provider_name);
            }

            if (isset($result->provider_abbreviation)) {
                $provider->setAbbreviation($result->provider_abbreviation);
            }

            if (isset($result->provider_wiki_url)) {
                $provider->setWikiURL($result->provider_wiki_url);
            }

            if (isset($result->provider_image_url)) {
                $provider->setImageURL($result->provider_image_url);
            }

            if (isset($result->provider_logo_url)) {
                $provider->setLogoURL($result->provider_logo_url);
            }

            $launch->setProvider($provider);
        }

        if (
            $detailed
            && isset($result->pad_slug)
        ) {
            $pad = new Pad();
            $pad->setSlug($result->pad_slug);

            if (isset($result->pad_id)) {
                $pad->setId($result->pad_id);
            }

            if (isset($result->pad_name)) {
                $pad->setName($result->pad_name);
            }

            if (isset($result->pad_wiki_url)) {
                $pad->setWikiURL($result->pad_wiki_url);
            }

            if (isset($result->pad_image_url)) {
                $pad->setImageURL($result->pad_image_url);
            }

            if (isset($result->pad_location_id)) {
                $pad->setLocation($this->locationManager->getLocationById($result->pad_location_id));
            }

            $launch->setPad($pad);
        }

        if (
            $detailed
        ) {
            $launch->setStatus($this->statusManager->getStatusByLaunchId($result->id));
        }

        if (
            isset($result->start_win_open, $result->start_win_close, $result->start_net)
            && $result->start_win_open !== null
            && $result->start_win_close !== null
            && $result->start_net !== null
        ) {
            $launchTime = new LaunchTime();

            $launchTime->setLaunchWinOpen($this->toDateTime($result->start_win_open));
            $launchTime->setLaunchWinClose($this->toDateTime($result->start_win_close));
            $launchTime->setLaunchNet($this->toDateTime($result->start_net));

            $launch->setLaunchTime($launchTime);
        }

        $launch->setLivestreamURL($result->livestream_url ?? null);
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
     * @param Status|null $status
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
        ?Status $status,
        ?LaunchTime $launchTime,
        array $tags,
        ?string $livestreamURL
    ): bool {
        $launch = $this->getLaunchBySlug(Utils::stringToSlug($name));

        if ($launch !== null) {
            return false;
        }

        DB::table(self::TABLE)->insert([
            Launch::KEY_NAME => $name,
            Launch::KEY_SLUG => Utils::stringToSlug($name),
            Launch::KEY_DESCRIPTION => $description,
            Launch::KEY_ROCKET_ID => $rocket === null ? null : $rocket->getId(),
            Launch::KEY_PROVIDER_ID => $provider === null ? null : $provider->getId(),
            Launch::KEY_PAD_ID => $pad === null ? null : $pad->getId(),
            Launch::KEY_STATUS_ID => $status === null ? null : $status->getId(),
            Launch::KEY_TAGS => json_encode($tags, JSON_THROW_ON_ERROR),
            Launch::KEY_LIVESTREAM_URL => $livestreamURL,
            LaunchTime::KEY_LAUNCH_NET => $launchTime === null ? null : $launchTime->getLaunchNet()->format("Y-m-d H:i:s"),
            LaunchTime::KEY_LAUNCH_WINDOW_OPEN => $launchTime === null ? null : $launchTime->getLaunchWinOpen()->format("Y-m-d H:i:s"),
            LaunchTime::KEY_LAUNCH_WINDOW_CLOSE => $launchTime === null ? null : $launchTime->getLaunchWinClose()->format("Y-m-d H:i:s"),
            Launch::KEY_PUBLISHED => false
        ]);
        return true;
    }

    /**
     * @param string $originalSlug
     * @param string|null $slug
     * @param string|null $name
     * @param string|null $description
     * @param Rocket|null $rocket
     * @param Pad|null $pad
     * @param Provider|null $provider
     * @param Status|null $launchStatus
     * @param LaunchTime|null $launchTime
     * @param array|null $tags
     * @param string|null $livestreamURL
     * @param bool|null $published
     * @return bool
     */
    public function updateLaunch(
        string $originalSlug,
        ?string $slug,
        ?string $name,
        ?string $description,
        ?Rocket $rocket,
        ?Pad $pad,
        ?Provider $provider,
        ?Status $launchStatus,
        ?LaunchTime $launchTime,
        ?array $tags,
        ?string $livestreamURL,
        ?bool $published
    ): bool {
        $launch = $this->getLaunchBySlug($originalSlug);

        if ($launch === null) {
            return false;
        }

        DB::table(self::TABLE)
            ->where(Launch::KEY_SLUG, "=", $originalSlug)
            ->update(
                $this->buildUpdateArray(
                    $slug,
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
     * @param string|null $slug
     * @param string|null $name
     * @param string|null $description
     * @param Rocket|null $rocket
     * @param Pad|null $pad
     * @param Provider|null $provider
     * @param Status|null $launchStatus
     * @param LaunchTime|null $launchTime
     * @param array|null $tags
     * @param string|null $livestreamURL
     * @param bool|null $published
     * @return array
     */
    private function buildUpdateArray(
        ?string $slug,
        ?string $name,
        ?string $description,
        ?Rocket $rocket,
        ?Pad $pad,
        ?Provider $provider,
        ?Status $launchStatus,
        ?LaunchTime $launchTime,
        ?array $tags,
        ?string $livestreamURL,
        ?bool $published
    ): array {
        $array = [];

        if ($slug !== null) {
            $array[Launch::KEY_SLUG] = $slug;
        }

        if ($name !== null) {
            $array[Launch::KEY_NAME] = $name;
        }

        if ($description !== null) {
            $array[Launch::KEY_DESCRIPTION] = $description;
        }

        if ($rocket !== null) {
            $array[Launch::KEY_ROCKET_ID] = $rocket->getId();
        }

        if ($pad !== null) {
            $array[Launch::KEY_PAD_ID] = $pad->getId();
        }

        if ($provider !== null) {
            $array[Launch::KEY_PROVIDER_ID] = $provider->getId();
        }

        if ($launchStatus !== null) {
            $array[Launch::KEY_STATUS_ID] = $launchStatus->getId();
        }

        if ($launchTime !== null) {
            $array[LaunchTime::KEY_LAUNCH_NET] = $launchTime->getLaunchNet()->format("Y-m-d H:i:s");
            $array[LaunchTime::KEY_LAUNCH_WINDOW_OPEN] = $launchTime->getLaunchWinOpen()->format("Y-m-d H:i:s");
            $array[LaunchTime::KEY_LAUNCH_WINDOW_CLOSE] = $launchTime->getLaunchWinClose()->format("Y-m-d H:i:s");
        }

        if (!isEmpty($tags)) {
            try {
                $array[Launch::KEY_TAGS] = json_encode($tags, JSON_THROW_ON_ERROR);
            } catch (\JsonException $ignored) { }
        }

        if ($livestreamURL !== null) {
            $array[Launch::KEY_LIVESTREAM_URL] = $livestreamURL;
        }

        if ($published !== null) {
            $array[Launch::KEY_PUBLISHED] = $published;
        }

        return $array;
    }
}
