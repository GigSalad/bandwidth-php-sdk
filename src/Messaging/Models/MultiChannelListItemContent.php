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

    // public static function fromArray(array $data): static
    // {
    //     // TODO Figure out how to determine what "type" of MultiChannelListItemContent this is?
    //     // Is this even where this should be done? How can it be done? Seems brittle...

    //     if (!empty($data["cardWidth"]) && !empty($data["cardContents"])) {
    //         return RbmCardCarousel::fromArray($data);
    //     } elseif (!empty($data["text"]) && !empty($data["media"])) {
    //         return Mms::fromArray($data);
    //     } elseif (!empty($data["text"])) {
    //         return Sms::fromArray($data);
    //     }
    // }

    public function validate(): void {}

    public function jsonSerialize(): array
    {
        $this->validate();

        return array_filter($this->toArray());
    }
}
