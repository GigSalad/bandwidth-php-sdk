<?php

namespace BandwidthLib\Messaging\Models;

use BandwidthLib\Messaging\Models\Contracts\ArrayConvertible;
use BandwidthLib\Messaging\Models\Traits\Builder;
use Exception;
use JsonSerializable;

/**
 * A set of suggested actions shown below RBM content.
 */
class RbmActions implements JsonSerializable, ArrayConvertible
{
    use Builder;

    public const int LIMIT = 11;

    /**
     * @param RbmAction[] $actions
     * @throws Exception when more than the limit of actions/suggestions are added
     */
    protected function __construct(protected array $actions = [])
    {
        if ($this->count() > static::LIMIT) {
            $this->throwTooManyItemsException();
        }
    }

    /**
     * @param mixed[] $data
     */
    public static function fromArray(array $data): static
    {
        return new static(static::actionsFromArray($data));
    }

    /**
     * @param mixed[] $data
     * @return null|static
     */
    public static function tryFromArray(array $data): ?static
    {
        try {
            return static::fromArray($data);
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * @param mixed[] $data
     * @return RbmAction[]
     */
    private static function actionsFromArray(array $data): array
    {
        return array_map(RbmAction::fromArray(...), $data);
    }

    private function throwTooManyItemsException(): void
    {
        $limit = static::LIMIT;

        throw new Exception(
            "RBM Card actions/suggestions cannot have over {$limit} items",
        );
    }

    /**
     * @throws Exception when attempting to add another item when at the limit
     */
    public function push(RbmAction $action): static
    {
        if ($this->isFull()) {
            $this->throwTooManyItemsException();
        }

        $this->actions[] = $action;
        return $this;
    }

    /**
     * @param string|mixed[] $postbackData
     */
    public function withReply(string $text, string|array $postbackData): static
    {
        $this->push(RbmAction::reply($text, $postbackData));
        return $this;
    }

    /**
     * @param string|mixed[] $postbackData
     */
    public function withDialPhone(
        string $text,
        string|array $postbackData,
        string $phoneNumber,
    ): static {
        $this->push(RbmAction::dialPhone($text, $postbackData, $phoneNumber));
        return $this;
    }

    /**
     * @param string|mixed[] $postbackData
     */
    public function withShowLocation(
        string $text,
        string|array $postbackData,
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

    /**
     * @param string|mixed[] $postbackData
     */
    public function withCreateCalendarEvent(
        string $text,
        string|array $postbackData,
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

    /**
     * @param string|mixed[] $postbackData
     */
    public function withOpenUrl(
        string $text,
        string|array $postbackData,
        string $url,
    ): static {
        $this->push(RbmAction::openUrl($text, $postbackData, $url));
        return $this;
    }

    /**
     * @param string|mixed[] $postbackData
     */
    public function withRequestLocation(
        string $text,
        string|array $postbackData,
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

    public function toArray(): array
    {
        return array_map(
            fn(RbmAction $action) => $action->toArray(),
            $this->actions,
        );
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
