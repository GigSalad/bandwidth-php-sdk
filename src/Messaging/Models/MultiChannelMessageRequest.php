<?php

namespace BandwidthLib\Messaging\Models;

use BandwidthLib\Messaging\Models\Traits\Builder;
use BandwidthLib\Messaging\Models\Traits\ToArray;

class MultiChannelMessageRequest implements \JsonSerializable
{
    use Builder, ToArray;

    public function __construct(
        /**
         * The phone number(s) the message should be sent to in E164 format
         * @required
         */
        protected string $to,

        /**
         * A list of message bodies. The messages will be attempted in the
         * order they are listed. Once a message sends successfully, the
         * others will be ignored.
         *
         * @var MultiChannelList $channelList public property
         */
        protected MultiChannelList $channelList,

        /**
         * A custom string that will be included in callback events of the message.
         * Max 1024 characters
         */
        protected string $tag = "",

        /**
         * Specifies the message's sending priority with respect to other messages in your account. For best results and optimal throughput, reserve the 'high' priority setting for critical messages only.
         */
        protected string $priority = "default",
    ) {}

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
