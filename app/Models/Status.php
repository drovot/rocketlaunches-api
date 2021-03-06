<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Utils\HasId;

class Status extends AbstractModel
{

    public const KEY_ID = 'id';
    public const KEY_LAUNCH_ID = 'launch_id';
    public const KEY_NAME = 'name';
    public const KEY_PROBABILITY = 'probability';
    public const KEY_TBD = 'tbd';

    use HasId;

    /** @var string|null */
    private ?string $launchId = null;

    /** @var string|null */
    private ?string $name = null;

    /** @var float */
    private float $probability = 0;

    /** @var bool|null */
    private ?bool $tbd = false;

    /**
     * @return array
     */
    public function export(): array
    {
        return [
            self::KEY_NAME => $this->name,
            self::KEY_PROBABILITY => $this->probability,
            self::KEY_TBD => $this->tbd
        ];
    }

    /**
     * @return string|null
     */
    public function getLaunchId(): ?string
    {
        return $this->launchId;
    }

    /**
     * @param string|null $launchId
     * @return self
     */
    public function setLaunchId(?string $launchId): self
    {
        $this->launchId = $launchId;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     * @return Status
     */
    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return float
     */
    public function getProbability()
    {
        return $this->probability;
    }

    /**
     * @param float $probability
     * @return Status
     */
    public function setProbability(float $probability): self
    {
        $this->probability = $probability;

        return $this;
    }

    /**
     * @return bool
     */
    public function isTBD(): bool
    {
        return $this->tbd ?? false;
    }

    /**
     * @param bool $tbd
     */
    public function setTBD(bool $tbd): void
    {
        $this->tbd = $tbd;
    }
}
