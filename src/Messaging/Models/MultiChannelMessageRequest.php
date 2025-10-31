<?php

namespace BandwidthLib\Messaging\Models;

use BandwidthLib\Messaging\Models\Traits\Builder;
use BandwidthLib\Messaging\Models\Traits\MissingProperties;
use BandwidthLib\Messaging\Models\Traits\ToArray;
use BandwidthLib\Messaging\Models\Enums\MessagePriority;

/**
 * A multi-channel message request used for sending RBM/RCS messages
 * with optionally chained "fallback" SMS messages.
 */
class MultiChannelMessageRequest implements \JsonSerializable
{
    use Builder, MissingProperties, ToArray;

    public function __construct(
        protected string $to = "",
        protected ?MultiChannelList $channelList = null,
        protected string $tag = "",
        protected MessagePriority $priority = MessagePriority::Default,
    ) {
        if (!$channelList) {
            $this->channelList = new MultiChannelList();
        }
    }

    /**
     * The phone number(s) the message should be sent to in E164
     * format
     */
    public function to(string $to): static
    {
        $this->to = $to;
        return $this;
    }

    /**
     * A list of message bodies. The messages will be attempted in
     * the order they are listed. Once a message sends
     * successfully, the others will be ignored.
     */
    public function channelList(MultiChannelList $channelList): static
    {
        $this->channelList = $channelList;
        return $this;
    }

    /**
     * A custom string that will be included in callback events of
     * the message.
     *
     * Max 1024 characters.
     */
    public function tag(string $tag): static
    {
        $this->tag = $tag;
        return $this;
    }

    /**
     * Specifies the message's sending priority with respect to
     * other messages in your account. For best results and optimal
     * throughput, reserve the 'high' priority setting for critical
     * messages only.
     */
    public function priority(MessagePriority $priority): static
    {
        $this->priority = $priority;
        return $this;
    }

    public function addContent(
        string $from,
        string $applicationId,
        MultiChannelListItemContent $content,
    ): static {
        $item = MultiChannelListItem::build()
            ->from($from)
            ->applicationId($applicationId)
            ->content($content);

        $this->channelList->addItem($item);

        return $this;
    }

    /**
     * Build a multi-channel (RBM) message request with a basic, and
     * perhaps the most common, structure: a single content item.
     */
    public static function basic(
        string $to,
        string $from,
        string $applicationId,
        \JsonSerializable $content,
    ): static {
        return static::build()
            ->to($to)
            ->addContent($from, $applicationId, $content);
    }

    public function jsonSerialize(): array
    {
        $this->throwIfMissingProperties();

        if (strlen($this->tag) > 1024) {
            throw new \Exception(
                "Multi-channel message tag cannot exceed 1024 characters",
            );
        }

        return $this->toArray();
    }
}
