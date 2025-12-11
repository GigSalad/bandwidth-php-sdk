<?php

namespace BandwidthLib\Messaging\Models;

use BandwidthLib\Messaging\Models\Contracts\ArrayConvertible;
use BandwidthLib\Messaging\Models\Traits\FromArray;
use BandwidthLib\Messaging\Models\Traits\ToArray;
use JsonSerializable;

/**
 * A mapping of the JSON response structure returned by the
 * multi-channel API.
 */
class BandwidthMultiChannelMessage implements JsonSerializable, ArrayConvertible
{
    use FromArray, ToArray;

    /**
     * @param mixed[] $links
     * @param mixed[] $data
     * @param mixed[] $errors
     */
    public function __construct(
        public array $links = [],
        public mixed $data = [],
        public array $errors = [],
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            $data["links"] ?? [],
            $data["data"] ?? [],
            $data["errors"] ?? [],
        );
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
