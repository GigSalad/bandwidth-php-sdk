<?php

namespace BandwidthLib\Messaging\Models;

use BandwidthLib\Messaging\Models\Enums\RbmMediaHeight;
use BandwidthLib\Messaging\Models\Traits\Builder;
use BandwidthLib\Messaging\Models\Traits\FromArray;
use BandwidthLib\Messaging\Models\Traits\ToArray;
use Exception;
use JsonSerializable;

class RbmMediaFile implements JsonSerializable
{
    use Builder, FromArray, ToArray;

    /**
     * @param RbmActions[] $suggestions
     */
    protected function __construct(
        protected ?RbmMediaHeight $height = null,
        protected string $fileUrl = "",
        protected string $thumbnailUrl = "",
    ) {}

    /**
     * @param mixed[] $data
     */
    public static function fromArray(array $data): static
    {
        return new static(
            RbmMediaHeight::from($data["height"]),
            $data["fileUrl"],
            $data["thumbnailUrl"],
        );
    }

    public function fileUrl(string $fileUrl): static
    {
        $this->fileUrl = $fileUrl;
        return $this;
    }

    public function thumbnailUrl(string $thumbnailUrl): static
    {
        $this->thumbnailUrl = $thumbnailUrl;
        return $this;
    }

    public function height(?RbmMediaHeight $height): static
    {
        $this->height = $height;
        return $this;
    }

    public function hasHeight(): bool
    {
        return !empty($this->height);
    }

    public function validate(): void
    {
        if (!$this->fileUrl) {
            throw new Exception("RBM media file must have a fileUrl.");
        }
    }

    public function jsonSerialize(): array
    {
        $this->validate();

        return array_filter($this->toArray());
    }
}
