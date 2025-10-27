<?php

namespace BandwidthLib\Messaging\Models;

use BandwidthLib\Messaging\Models\Traits\Builder;
use BandwidthLib\Messaging\Models\Traits\MissingProperties;
use BandwidthLib\Messaging\Models\Traits\ToArray;

class MultiChannelListItem implements \JsonSerializable
{
    use Builder, MissingProperties, ToArray;

    private function __construct(
        protected string $from = "",
        protected string $applicationId = "",
        protected string $channel = "RBM",
        protected \JsonSerializable|null $content = null,
    ) {}

    public function from(string $from): static
    {
        $this->from = $from;
        return $this;
    }

    public function applicationId(string $applicationId): static
    {
        $this->applicationId = $applicationId;
        return $this;
    }

    public function content(\JsonSerializable $content): static
    {
        $this->content = $content;
        return $this;
    }

    public function channel(string $channel): static
    {
        $this->channel = $channel;
        return $this;
    }

    /**
     * @throws \Exception when any properties are missing
     */
    public function jsonSerialize(): array
    {
        $this->throwIfMissingProperties();

        return $this->toArray();
    }
}
