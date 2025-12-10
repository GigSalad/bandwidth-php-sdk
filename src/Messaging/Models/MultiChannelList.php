<?php

namespace BandwidthLib\Messaging\Models;

use BandwidthLib\Messaging\Models\Contracts\ArrayConvertible;
use BandwidthLib\Messaging\Models\Traits\Builder;
use BandwidthLib\Messaging\Models\Traits\FromArray;
use Exception;
use JsonSerializable;

class MultiChannelList implements JsonSerializable, ArrayConvertible
{
    use Builder, FromArray;

    public const int LIMIT = 4;

    /**
     * @param MultiChannelListItem[] $items
     * @throws Exception when more than the limit of items are added
     */
    public function __construct(protected array $items = [])
    {
        if ($this->count() > static::LIMIT) {
            $this->throwTooManyItemsException();
        }
    }

    public static function fromArray(array $data): static
    {
        $items = array_map(MultiChannelListItem::fromArray(...), $data);
        return new static($items);
    }

    private function throwTooManyItemsException(): void
    {
        $limit = static::LIMIT;

        throw new Exception(
            "Multi-channel list cannot have over {$limit} items",
        );
    }

    /**
     * @throws Exception when attempting to add another item when at the limit
     */
    public function push(MultiChannelListItem $item): void
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
            throw new Exception("Multi-channel list cannot be empty");
        }
    }

    public function toArray(): array
    {
        $this->validate();
        return $this->items;
    }

    public function jsonSerialize(): array
    {
        return array_filter($this->toArray());
    }
}
