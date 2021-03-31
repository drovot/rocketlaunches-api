<?php

declare(strict_types=1);

namespace App\Models;

use DateTime;

class LaunchTime extends AbstractModel
{

    /** @var DateTime|null  */
    private ?DateTime $launchWinOpen = null;

    /** @var DateTime|null  */
    private ?DateTime $launchWinClose = null;

    /** @var DateTime|null  */
    private ?DateTime $launchNet = null;

    /**
     * @return array
     */
    public function export(): array
    {
        return [
            "launchWinOpen" => $this->launchWinOpen,
            "launchNet" => $this->launchNet,
            "launchWinClose" => $this->launchWinClose
        ];
    }

    /**
     * @return DateTime
     */
    public function getLaunchWinOpen(): DateTime
    {
        return $this->launchWinOpen;
    }

    /**
     * @return DateTime
     */
    public function getLaunchWinClose(): DateTime
    {
        return $this->launchWinClose;
    }

    /**
     * @return DateTime
     */
    public function getLaunchNet(): DateTime
    {
        return $this->launchNet;
    }

    /**
     * @param DateTime $launchNet
     * @return self
     */
    public function setLaunchNet(DateTime $launchNet): self
    {
        $this->launchNet = $launchNet;

        return $this;
    }

    /**
     * @param DateTime $launchWinClose
     * @return self
     */
    public function setLaunchWinClose(DateTime $launchWinClose): self
    {
        $this->launchWinClose = $launchWinClose;

        return $this;
    }

    /**
     * @param DateTime $launchWinOpen
     * @return self
     */
    public function setLaunchWinOpen(DateTime $launchWinOpen): self
    {
        $this->launchWinOpen = $launchWinOpen;

        return $this;
    }
}
