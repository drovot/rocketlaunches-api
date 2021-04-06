<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Utils\HasNameSlug;
use App\Models\Utils\HasId;

class Rocket extends AbstractModel
{

    public const KEY_ID = 'id';
    public const KEY_NAME = 'name';
    public const KEY_SLUG = 'slug';
    public const KEY_WIKI_URL = 'wiki_url';
    public const KEY_IMAGE_URL = 'image_url';

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
            self::KEY_ID => $this->id,
            self::KEY_NAME => $this->name,
            self::KEY_SLUG => $this->slug,
            self::KEY_WIKI_URL => $this->wikiURL,
            self::KEY_IMAGE_URL => $this->imageURL,
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
