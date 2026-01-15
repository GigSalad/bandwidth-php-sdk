<?php

namespace BandwidthLib\Messaging\Models;

use BandwidthLib\Messaging\Models\Contracts\ArrayConvertible;
use BandwidthLib\Messaging\Models\Traits\Builder;
use BandwidthLib\Messaging\Models\Traits\FromArray;
use BandwidthLib\Messaging\Models\Traits\ToArray;
use JsonSerializable;

/**
 * Abstract class for the "real" content types to extend.
 */
abstract class MultiChannelListItemContent implements
    JsonSerializable,
    ArrayConvertible
{
    use Builder, FromArray, ToArray;

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
