<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Utils\HasId;

class LaunchStatus extends AbstractModel
{

    use HasId;

    /** @var string|null  */
    private ?string $displayName = null;

    /** @var bool|null  */
    private ?bool $cancelled = false;

    /**
     * @return array
     */
    public function export(): array
    {
        return [
            "id" => $this->id,
            "displayName" => $this->displayName,
            "cancelled" => $this->cancelled
        ];
    }

    /**
     * @return string
     */
    public function getDisplayName(): string
    {
        return $this->displayName;
    }

    /**
     * @return bool
     */
    public function isCancelled(): bool
    {
        return $this->cancelled;
    }

    /**
     * @param bool $cancelled
     * @return self
     */
    public function setCancelled(bool $cancelled): self
    {
        $this->cancelled = $cancelled;

        return $this;
    }

    /**
     * @param string $displayName
     * @return self
     */
    public function setDisplayName(string $displayName): self
    {
        $this->displayName = $displayName;

        return $this;
    }
}
