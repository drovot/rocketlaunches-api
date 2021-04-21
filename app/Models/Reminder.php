<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Utils\HasId;

class Reminder extends AbstractModel
{

    public const KEY_ID = 'id';
    public const KEY_TITLE = 'title';
    public const KEY_LAUNCH = 'launch';
    public const KEY_USER = 'user';
    public const KEY_USER_ID = 'user_id';

    use HasId;

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

    public function export()
    {
        return [
            self::KEY_ID => $this->id,
            self::KEY_TITLE => $this->title,
            self::KEY_LAUNCH => $this->launch,
            self::KEY_USER => $this->user->export()
        ];
    }
}
