<?php

namespace BandwidthLib\Messaging\Models;

use BandwidthLib\Messaging\Models\Contracts\ArrayConvertible;
use BandwidthLib\Messaging\Models\Enums\MessageChannel;
use BandwidthLib\Messaging\Models\Traits\Builder;
use BandwidthLib\Messaging\Models\Traits\FromArray;
use BandwidthLib\Messaging\Models\Traits\MissingProperties;
use BandwidthLib\Messaging\Models\Traits\ToArray;
use Exception;
use JsonSerializable;

class MultiChannelListItem implements JsonSerializable, ArrayConvertible
{
    use Builder, FromArray, MissingProperties, ToArray;

    protected function __construct(
        protected string $from = "",
        protected string $applicationId = "",
        protected ?MessageChannel $channel = null,
        protected ?MultiChannelListItemContent $content = null,
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

    public function content(MultiChannelListItemContent $content): static
    {
        $this->content = $content;
        $this->determineChannel();
        return $this;
    }

    public function rbmText(RbmText $text): static
    {
        return $this->content($text);
    }

    public function rbmMedia(RbmMedia $media): static
    {
        return $this->content($media);
    }

    public function rbmCardStandalone(RbmCardStandalone $card): static
    {
        return $this->content($card);
    }

    public function sms(Sms $sms): static
    {
        return $this->content($sms);
    }

    public function mms(Mms $mms): static
    {
        return $this->content($mms);
    }

    /**
     * @throws Exception when channel for content cannot be determined
     */
    protected function determineChannel(): void
    {
        $this->channel = match (true) {
            $this->content instanceof RbmText,
            $this->content instanceof RbmMedia,
            $this->content instanceof RbmCardStandalone,
            $this->content instanceof RbmCardCarousel
                => MessageChannel::RBM,
            $this->content instanceof Sms => MessageChannel::SMS,
            $this->content instanceof Mms => MessageChannel::MMS,
            default => null,
        };

        if (!$this->channel) {
            throw new Exception(
                "Could not determine list item 'channel' value.",
            );
        }
    }

    /**
     * @throws Exception when any properties are missing
     */
    public function jsonSerialize(): array
    {
        $this->throwIfMissingProperties();

        return $this->toArray();
    }
}
