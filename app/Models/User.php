<?php

declare(strict_types=1);

namespace App\Models;

class User extends AbstractModel
{

    public const KEY_ID = 'id';
    public const KEY_API_TOKEN = 'api_token';
    public const KEY_FIRST_NAME = 'first_name';
    public const KEY_LAST_NAME = 'last_name';
    public const KEY_EMAIL = 'email';

    /** @var int */
    private int $id;

    /** @var string */
    private string $apiToken;

    /** @var string */
    private string $firstname;

    /** @var string */
    private string $lastname;

    /** @var string */
    private string $email;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getApiToken(): string
    {
        return $this->apiToken;
    }

    /**
     * @return string
     */
    public function getFirstname(): string
    {
        return $this->firstname;
    }

    /**
     * @return string
     */
    public function getLastname(): string
    {
        return $this->lastname;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $apiToken
     * @return self
     */
    public function setApiToken(string $apiToken): self
    {
        $this->apiToken = $apiToken;

        return $this;
    }

    /**
     * @param int $id
     * @return self
     */
    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @param string $firstname
     * @return self
     */
    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * @param string $lastname
     * @return self
     */
    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * @param string $email
     * @return self
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return array
     */
    public function export(): array
    {
        return [
            self::KEY_ID => $this->id,
            self::KEY_API_TOKEN => $this->apiToken,
            self::KEY_FIRST_NAME => $this->firstname,
            self::KEY_LAST_NAME => $this->lastname,
            self::KEY_EMAIL => $this->email,
        ];
    }
}
