<?php

declare(strict_types=1);

namespace App\Models;

use App\Http\Managers\Defaults;
use App\Models\Utils\HasNameSlug;
use App\Models\Utils\HasId;

class Launch extends AbstractModel
{

    public const KEY_ID = 'id';
    public const KEY_NAME = 'name';
    public const KEY_SLUG = 'slug';
    public const KEY_DESCRIPTION = 'description';
    public const KEY_STATUS = 'status';
    public const KEY_ROCKET = 'rocket';
    public const KEY_PROVIDER = 'provider';
    public const KEY_PAD = 'pad';
    public const KEY_TAGS = 'tags';
    public const KEY_LIVESTREAM_URL = 'livestream_url';
    public const KEY_LAUNCH_TIME = 'launch_time';
    public const KEY_PUBLISHED = 'published';
    public const KEY_DETAILED = 'detailed';

    public const KEY_STATUS_ID = 'status_id';
    public const KEY_ROCKET_ID = 'rocket_id';
    public const KEY_PROVIDER_ID = 'provider_id';
    public const KEY_PAD_ID = 'pad_id';

    use HasId;
    use HasNameSlug;

    /** @var string|null  */
    private ?string $description = null;

    /** @var LaunchStatus|null  */
    private ?LaunchStatus $status = null;

    /** @var Rocket|null  */
    private ?Rocket $rocket = null;

    /** @var Provider|null  */
    private ?Provider $provider = null;

    /** @var Pad|null  */
    private ?Pad $pad = null;

    /** @var array  */
    private array $tags = [];

    /** @var string|null  */
    private ?string $livestreamURL = null;

    /** @var LaunchTime|null  */
    private ?LaunchTime $launchTime = null;

    /** @var bool  */
    private bool $published = Defaults::STATUS_PUBLISHED;

    /** @var bool  */
    private bool $detailed = Defaults::REQUEST_DETAILED;

    /**
     * @return array
     */
    public function export(): array
    {
        if (!$this->isPublished()) {
            return $this->isDetailed() ?
                [
                    self::KEY_ID => $this->id,
                    self::KEY_NAME => $this->name,
                    self::KEY_SLUG => $this->slug,
                    self::KEY_DESCRIPTION => $this->description ?? null,
                    self::KEY_STATUS => $this->status !== null ? $this->status->export() : null,
                    self::KEY_ROCKET => $this->rocket !== null ? $this->rocket->export() : null,
                    self::KEY_PROVIDER => $this->provider !== null ? $this->provider->export() : null,
                    self::KEY_PAD => $this->pad !== null ? $this->pad->export() : null,
                    self::KEY_TAGS => $this->tags,
                    self::KEY_LIVESTREAM_URL => $this->livestreamURL ?? null,
                    self::KEY_LAUNCH_TIME => $this->launchTime !== null ? $this->launchTime->export() : null,
                    self::KEY_PUBLISHED => $this->published
                ]
                :
                [
                    self::KEY_ID => $this->id,
                    self::KEY_NAME => $this->name,
                    self::KEY_SLUG => $this->slug,
                    self::KEY_DESCRIPTION => $this->description ?? null,
                    self::KEY_TAGS => $this->tags,
                    self::KEY_PUBLISHED => $this->published
                ];
        }

        return $this->isDetailed() ?
            [
                self::KEY_ID => $this->id,
                self::KEY_NAME => $this->name,
                self::KEY_SLUG => $this->slug,
                self::KEY_DESCRIPTION => $this->description ?? null,
                self::KEY_STATUS => $this->status !== null ? $this->status->export() : null,
                self::KEY_ROCKET => $this->rocket !== null ? $this->rocket->export() : null,
                self::KEY_PROVIDER => $this->provider !== null ? $this->provider->export() : null,
                self::KEY_PAD => $this->pad !== null ? $this->pad->export() : null,
                self::KEY_TAGS => $this->tags,
                self::KEY_LIVESTREAM_URL => $this->livestreamURL ?? null,
                self::KEY_LAUNCH_TIME => $this->launchTime !== null ? $this->launchTime->export() : null
            ]
            :
            [
                self::KEY_ID => $this->id,
                self::KEY_NAME => $this->name,
                self::KEY_SLUG => $this->slug,
                self::KEY_DESCRIPTION => $this->description ?? null,
                self::KEY_TAGS => $this->tags
            ];
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @return LaunchStatus|null
     */
    public function getStatus(): ?LaunchStatus
    {
        return $this->status;
    }

    /**
     * @return LaunchTime|null
     */
    public function getLaunchTime(): ?LaunchTime
    {
        return $this->launchTime;
    }

    /**
     * @return string|null
     */
    public function getLivestreamURL(): ?string
    {
        return $this->livestreamURL;
    }

    /**
     * @return Pad|null
     */
    public function getPad(): ?Pad
    {
        return $this->pad;
    }

    /**
     * @return Provider|null
     */
    public function getProvider(): ?Provider
    {
        return $this->provider;
    }

    /**
     * @return Rocket|null
     */
    public function getRocket(): ?Rocket
    {
        return $this->rocket;
    }

    /**
     * @return array
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    public function hasLivestream(): bool
    {
        return $this->livestreamURL !== null;
    }

    /**
     * @return bool
     */
    public function isPublished(): bool
    {
        return $this->published;
    }

    /**
     * @return bool
     */
    public function isDetailed(): bool
    {
        return $this->detailed;
    }

    /**
     * @param string $description
     * @return self
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @param LaunchTime $launchTime
     * @return self
     */
    public function setLaunchTime(LaunchTime $launchTime): self
    {
        $this->launchTime = $launchTime;

        return $this;
    }

    /**
     * @param Pad $pad
     * @return self
     */
    public function setPad(Pad $pad): self
    {
        $this->pad = $pad;

        return $this;
    }

    /**
     * @param Provider $provider
     * @return self
     */
    public function setProvider(Provider $provider): self
    {
        $this->provider = $provider;

        return $this;
    }

    /**
     * @param Rocket $rocket
     * @return self
     */
    public function setRocket(Rocket $rocket): self
    {
        $this->rocket = $rocket;

        return $this;
    }

    /**
     * @param LaunchStatus $status
     * @return self
     */
    public function setStatus(LaunchStatus $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @param array $tags
     * @return self
     */
    public function setTags(array $tags): self
    {
        $this->tags = $tags;

        return $this;
    }

    /**
     * @param string|null $livestreamURL
     * @return self
     */
    public function setLivestreamURL(?string $livestreamURL): self
    {
        $this->livestreamURL = $livestreamURL;

        return $this;
    }

    /**
     * @param bool $published
     * @return self
     */
    public function setPublished(bool $published): self
    {
        $this->published = $published;

        return $this;
    }

    /**
     * @param bool $detailed
     * @return self
     */
    public function setDetailed(bool $detailed): self
    {
        $this->detailed = $detailed;

        return $this;
    }
}
