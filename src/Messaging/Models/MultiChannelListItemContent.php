<?php

namespace BandwidthLib\Messaging\Models;

use BandwidthLib\Messaging\Models\Traits\Builder;
use BandwidthLib\Messaging\Models\Traits\ToArray;

class MultiChannelListItemContent implements \JsonSerializable
{
    use Builder, ToArray;

    public function __construct()
    {
        // TODO
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
