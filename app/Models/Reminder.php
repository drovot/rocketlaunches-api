<?php

declare(strict_types=1);

namespace App\Models;

class Reminder
{

    /** @var string */
    private string $title;

    /** @var Launch */
    private Launch $launch;

    private User $user;

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return Launch
     */
    public function getLaunch(): Launch
    {
        return $this->launch;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param string $title
     * @return self
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @param Launch $launch
     * @return self
     */
    public function setLaunch(Launch $launch): self
    {
        $this->launch = $launch;

        return $this;
    }

    /**
     * @param User $user
     * @return self
     */
    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
