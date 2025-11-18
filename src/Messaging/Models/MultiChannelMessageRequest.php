<?php

namespace BandwidthLib\Messaging\Models;

use BandwidthLib\Messaging\Models\Contracts\ArrayConvertible;
use BandwidthLib\Messaging\Models\Traits\Builder;
use BandwidthLib\Messaging\Models\Traits\FromArray;
use BandwidthLib\Messaging\Models\Enums\MessagePriority;
use BandwidthLib\Messaging\Models\Traits\ToArray;
use Exception;
use JsonSerializable;

/**
 * A multi-channel message request used for sending RBM/RCS messages
 * with optionally chained "fallback" SMS/MMS messages.
 *
 * See: https://dev.bandwidth.com/apis/messaging-apis/messaging/#tag/Multi-Channel/operation/createMultiChannelMessage
 */
class MultiChannelMessageRequest implements JsonSerializable, ArrayConvertible
{
    use Builder, FromArray, ToArray;

    protected function __construct(
        protected string $to = "",
        protected ?MultiChannelList $channelList = null,
        protected string $tag = "",
        protected ?MessagePriority $priority = null,
        protected string $expiration = "",
    ) {
        if (!$channelList) {
            $this->channelList = MultiChannelList::build();
        }
    }

    /**
     * Build a basic multi-channel message request with given content.
     */
    public static function basic(
        string $to,
        string $from,
        string $applicationId,
        MultiChannelListItemContent $content,
    ): static {
        return static::build()
            ->to($to)
            ->withContent($from, $applicationId, $content);
    }

    /**
     * Build a multi-channel message request with given RBM content.
     */
    public static function rbm(
        string $to,
        string $from,
        string $applicationId,
        RbmText|RbmMedia|RbmCardStandalone|RbmCardCarousel $rbmContent,
    ): static {
        return static::basic($to, $from, $applicationId, $rbmContent);
    }

    /**
     * Build a multi-channel message request with given SMS content.
     */
    public static function sms(
        string $to,
        string $from,
        string $applicationId,
        Sms $sms,
    ): static {
        return static::basic($to, $from, $applicationId, $sms);
    }

    /**
     * Build a multi-channel message request with given MMS content.
     */
    public static function mms(
        string $to,
        string $from,
        string $applicationId,
        Mms $mms,
    ): static {
        return static::basic($to, $from, $applicationId, $mms);
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
     * @throws Exception when tag is longer than 1024 characters
     */
    public function tag(string $tag): static
    {
        if (strlen($tag) > 1024) {
            throw new Exception(
                "Multi-channel message tag cannot exceed 1024 characters",
            );
        }

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

    /**
     * Build a multi-channel list item and appends it to this
     * multi-channel request's "channel list."
     */
    public function withContent(
        string $from,
        string $applicationId,
        MultiChannelListItemContent $content,
    ): static {
        $item = MultiChannelListItem::build()
            ->from($from)
            ->applicationId($applicationId)
            ->content($content);

        $this->channelList->push($item);

        return $this;
    }

    public function withRbm(
        string $from,
        string $applicationId,
        RbmText|RbmMedia|RbmCardStandalone|RbmCardCarousel $rbm,
    ): static {
        $this->withContent($from, $applicationId, $rbm);
        return $this;
    }

    public function withSms(
        string $from,
        string $applicationId,
        Sms $sms,
    ): static {
        $this->withContent($from, $applicationId, $sms);
        return $this;
    }

    public function withMms(
        string $from,
        string $applicationId,
        Mms $mms,
    ): static {
        $this->withContent($from, $applicationId, $mms);
        return $this;
    }

    public function validate(): void
    {
        if (!$this->to || $this->channelList->isEmpty()) {
            throw new Exception(
                "Multi-channel message request must have a 'to' recipient and one or more items",
            );
        }
    }

    public function jsonSerialize(): array
    {
        $this->validate();

        return array_filter($this->toArray());
    }
}
