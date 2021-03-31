<?php

declare(strict_types=1);

namespace App\Models\Utils;

trait HasId
{

    /** @var string|int|null */
    private $id = null;

    /**
     * @return string|int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string|int|null $id
     * @return self
     */
    public function setId($id): self
    {
        $this->id = $id;

        return $this;
    }
}
