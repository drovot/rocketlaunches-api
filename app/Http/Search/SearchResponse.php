<?php

declare(strict_types=1);

namespace App\Http\Search;

use App\Models\AbstractModel;

class SearchResponse extends AbstractModel
{

    /** @var string  */
    private string $title;

    /** @var string  */
    private string $subtitle;

    /** @var string  */
    private string $path;

    /** @var string|null  */
    private ?string $imageURL = null;

    /** @var string  */
    private string $category;

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getSubtitle(): string
    {
        return $this->subtitle;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return string|null
     */
    public function getImageURL(): ?string
    {
        return $this->imageURL;
    }

    /**
     * @return string
     */
    public function getCategory(): string
    {
        return $this->category;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @param string $subtitle
     */
    public function setSubtitle(string $subtitle): void
    {
        $this->subtitle = $subtitle;
    }

    /**
     * @param string $path
     */
    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    /**
     * @param string|null $imageURL
     */
    public function setImageURL(?string $imageURL): void
    {
        $this->imageURL = $imageURL;
    }

    /**
     * @param string $category
     */
    public function setCategory(string $category): void
    {
        $this->category = $category;
    }

    /**
     * @return array
     */
    public function export(): array
    {
        return [
            "title" => $this->title,
            "subtitle" => $this->subtitle,
            "path" => $this->path,
            "imageURL" => $this->imageURL,
            "category" => $this->category
        ];
    }
}
