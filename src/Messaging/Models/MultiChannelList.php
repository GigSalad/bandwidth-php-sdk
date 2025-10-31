<?php

namespace BandwidthLib\Messaging\Models;

use BandwidthLib\Messaging\Models\Traits\Builder;

class MultiChannelList implements \JsonSerializable
{
    use Builder;

    public const int LIMIT = 4;

    /**
     * @param \JsonSerializable[] $items
     * @throws \Exception when more than the limit of items are added
     */
    public function __construct(protected array $items = [])
    {
        if ($this->count() > static::LIMIT) {
            $this->throwTooManyItemsException();
        }
    }

    private function throwTooManyItemsException(): void
    {
        $limit = static::LIMIT;

        throw new \Exception(
            "Multi-channel list cannot have over {$limit} items",
        );
    }

    /**
     * @throws \Exception when attempting to add another item when at the limit
     */
    public function push(\JsonSerializable $item): void
    {
        if ($this->isFull()) {
            $this->throwTooManyItemsException();
        }

        $this->items[] = $item;
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    public function isFull(): bool
    {
        return $this->count() >= static::LIMIT;
    }

    public function validate(): void
    {
        if ($this->isEmpty()) {
            throw new \Exception("Multi-channel list cannot be empty");
        }
    }

    public function jsonSerialize(): array
    {
        $this->validate();

        return $this->items;
    }
}
