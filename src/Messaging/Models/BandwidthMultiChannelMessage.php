<?php

namespace BandwidthLib\Messaging\Models;

use BandwidthLib\Messaging\Models\Traits\ToArray;

/**
 * A mapping of the JSON response structure returned by the
 * multi-channel API.
 */
class BandwidthMultiChannelMessage implements \JsonSerializable
{
    use ToArray;

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

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
