<?php

namespace BandwidthLib\Messaging\Models;

use BandwidthLib\Messaging\Models\Traits\ToArray;

class BandwidthMultiChannelMessage implements \JsonSerializable
{
    use ToArray;

    public function __construct()
    {
        // TODO
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
