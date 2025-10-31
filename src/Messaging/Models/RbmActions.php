<?php

namespace BandwidthLib\Messaging\Models;

use BandwidthLib\Messaging\Models\Traits\Builder;

/**
 * A set of suggested actions shown below RBM content.
 */
class RbmActions implements \JsonSerializable
{
    use Builder;

    public const int LIMIT = 11;

    /**
     * @param RbmAction[] $actions
     * @throws \Exception when more than the limit of actions/suggestions are added
     */
    protected function __construct(protected array $actions = [])
    {
        if ($this->count() > static::LIMIT) {
            $this->throwTooManyItemsException();
        }
    }

    private function throwTooManyItemsException(): void
    {
        $limit = static::LIMIT;

        throw new \Exception(
            "RBM Card actions/suggestions cannot have over {$limit} items",
        );
    }

    /**
     * @throws \Exception when attempting to add another item when at the limit
     */
    public function push(RbmAction $action): static
    {
        if ($this->isFull()) {
            $this->throwTooManyItemsException();
        }

        $this->actions[] = $action;
        return $this;
    }

    public function withReply(string $text, string $postbackData): static
    {
        $this->push(RbmAction::reply($text, $postbackData));
        return $this;
    }

    public function withDialPhone(
        string $text,
        string $postbackData,
        string $phoneNumber,
    ): static {
        $this->push(RbmAction::dialPhone($text, $postbackData, $phoneNumber));
        return $this;
    }

    public function withShowLocation(
        string $text,
        string $postbackData,
        string $latitude,
        string $longitude,
        string $label = "",
    ): static {
        $this->push(
            RbmAction::showLocation(
                $text,
                $postbackData,
                $latitude,
                $longitude,
                $label,
            ),
        );

        return $this;
    }

    public function withCreateCalendarEvent(
        string $text,
        string $postbackData,
        string $title,
        string $startTime,
        string $endTime,
        string $description = "",
    ): static {
        $this->push(
            RbmAction::createCalendarEvent(
                $text,
                $postbackData,
                $title,
                $startTime,
                $endTime,
                $description,
            ),
        );

        return $this;
    }

    public function withOpenUrl(
        string $text,
        string $postbackData,
        string $url,
    ): static {
        $this->push(RbmAction::openUrl($text, $postbackData, $url));
        return $this;
    }

    public function withRequestLocation(
        string $text,
        string $postbackData,
    ): static {
        $this->push(RbmAction::requestLocation($text, $postbackData));
        return $this;
    }

    public function count(): int
    {
        return count($this->actions);
    }

    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    public function isFull(): bool
    {
        return $this->count() >= static::LIMIT;
    }

    public function jsonSerialize(): array
    {
        return $this->actions;
    }
}
