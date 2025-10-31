<?php

namespace BandwidthLib\Messaging\Models;

use BandwidthLib\Messaging\Models\Traits\Builder;
use BandwidthLib\Messaging\Models\Traits\ToArray;

/**
 * Abstract class for the "real" content types to extend.
 */
abstract class MultiChannelListItemContent implements \JsonSerializable
{
    use Builder, ToArray;

    public function validate(): void {}

    public function jsonSerialize(): array
    {
        $this->validate();

        return array_filter($this->toArray());
    }
}
