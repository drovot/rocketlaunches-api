<?php

declare(strict_types=1);

namespace App\Models;


use App\Models\Utils\HasNameSlug;
use App\Models\Utils\HasId;

class Provider extends AbstractModel
{

    use HasId;
    use HasNameSlug;

    /** @var string|null  */
    private ?string $abbreviation = null;

    /** @var string|null  */
    private ?string $wikiURL = null;

    /** @var string|null  */
    private ?string $imageURL = null;

    /** @var string|null  */
    private ?string $logoURL = null;

    /**
     * @return array
     */
    public function export(): array
    {
        return [
            "id" => $this->id,
            "name" => $this->name,
            "slug" => $this->slug,
            "abbreviation" => $this->abbreviation,
            "wikiURL" => $this->wikiURL,
            "imageURL" => $this->imageURL,
            "logoURL" => $this->logoURL,
        ];
    }

    /**
     * @return string|null
     */
    public function getAbbreviation(): ?string
    {
        return $this->abbreviation;
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
     * @return string|null
     */
    public function getLogoURL(): ?string
    {
        return $this->logoURL;
    }

    /**
     * @param string|null $abbreviation
     * @return self
     */
    public function setAbbreviation(?string $abbreviation): self
    {
        $this->abbreviation = $abbreviation;

        return $this;
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

    /**
     * @param string|null $logoURL
     * @return self
     */
    public function setLogoURL(?string $logoURL): self
    {
        $this->logoURL = $logoURL;

        return $this;
    }
}
