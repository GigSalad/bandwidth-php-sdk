<?php

namespace BandwidthLib\Messaging\Models;

use BandwidthLib\Messaging\Models\Enums\MessageChannel;
use BandwidthLib\Messaging\Models\Traits\Builder;
use BandwidthLib\Messaging\Models\Traits\MissingProperties;
use BandwidthLib\Messaging\Models\Traits\ToArray;

class MultiChannelListItem implements \JsonSerializable
{
    use Builder, MissingProperties, ToArray;

    protected static MessageChannel|string|null $channel = null;

    protected function __construct(
        protected string $from = "",
        protected string $applicationId = "",
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

    /**
     * If an arbitrary content structure is created and provided, we
     * can support its new or custom channel via this method. The
     * channel provided here will be used when the content's type
     * cannot be determined, later.
     */
    public function channel(MessageChannel|string $channel): void
    {
        static::$channel = $channel;
    }

    public function rbmText(RbmText $text): static
    {
        $this->content = $text;
        return $this;
    }

    public function rbmMedia(RbmMedia $media): static
    {
        $this->content = $media;
        return $this;
    }

    public function rbmCardStandalone(RbmCardStandalone $card): static
    {
        $this->content = $card;
        return $this;
    }

    public function sms(Sms $sms): static
    {
        $this->content = $sms;
        return $this;
    }

    public function mms(Mms $mms): static
    {
        $this->content = $mms;
        return $this;
    }

    /**
     * @throws \Exception when channel for content cannot be determined
     */
    protected function determineChannel(): MessageChannel
    {
        $channel = match (true) {
            $this->content instanceof RbmText,
            $this->content instanceof RbmMedia,
            $this->content instanceof RbmCardStandalone,
            $this->content instanceof RbmCardCarousel
                => MessageChannel::RBM,
            $this->content instanceof Sms => MessageChannel::SMS,
            $this->content instanceof Mms => MessageChannel::MMS,
            default => static::$channel,
        };

        if (!$channel) {
            throw new \Exception(
                "Multi-channel item must have a 'channel' value.",
            );
        }

        return $channel;
    }

    /**
     * @throws \Exception when any properties are missing
     */
    public function jsonSerialize(): array
    {
        $this->throwIfMissingProperties();

        return [
            "channel" => $this->determineChannel(),
            ...$this->toArray(),
        ];
    }
}
