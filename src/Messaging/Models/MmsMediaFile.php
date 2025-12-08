<?php

namespace BandwidthLib\Messaging\Models;

use BandwidthLib\Messaging\Models\Traits\Builder;
use BandwidthLib\Messaging\Models\Traits\FromArray;
use BandwidthLib\Messaging\Models\Traits\ToArray;
use Exception;
use JsonSerializable;

class MmsMediaFile implements JsonSerializable
{
    use Builder, FromArray, ToArray;

    /**
     * @param RbmActions[] $suggestions
     */
    protected function __construct(protected string $fileUrl = "") {}

    /**
     * @param mixed[] $data
     */
    public static function fromArray(array $data): static
    {
        return static::__construct($data["fileUrl"] ?? "");
    }

    public function fileUrl(string $fileUrl): static
    {
        $this->fileUrl = $fileUrl;
        return $this;
    }

    public function validate(): void
    {
        if (!$this->fileUrl) {
            throw new Exception("MMS media file must have a fileUrl.");
        }
    }

    public function jsonSerialize(): array
    {
        $this->validate();

        return array_filter($this->toArray());
    }
}
