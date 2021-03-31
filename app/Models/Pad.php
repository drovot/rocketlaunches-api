<?php

declare(strict_types=1);

namespace App\Models;


use App\Models\Utils\HasNameSlug;
use App\Models\Utils\HasId;

class Pad extends AbstractModel
{

    use HasId;
    use HasNameSlug;

    /** @var string|null  */
    private ?string $wikiURL = null;

    /** @var string|null  */
    private ?string $imageURL = null;

    /**
     * @return array
     */
    public function export(): array
    {
        return [
            "id" => $this->id,
            "name" => $this->name,
            "slug" => $this->slug,
            "wikiURL" => $this->wikiURL,
            "imageURL" => $this->imageURL,
        ];
    }

    /**
     * @return string|null
     */
    public function getWikiURL(): ?string
    {
        return $this->wikiURL;
    }

    /**
     * @return string|null
     */
    public function getImageURL(): ?string
    {
        return $this->imageURL;
    }

    /**
     * @param string|null $wikiURL
     * @return self
     */
    public function setWikiURL(?string $wikiURL): self
    {
        $this->wikiURL = $wikiURL;

        return $this;
    }

    /**
     * @param string|null $imageURL
     * @return self
     */
    public function setImageURL(?string $imageURL): self
    {
        $this->imageURL = $imageURL;

        return $this;
    }
}
