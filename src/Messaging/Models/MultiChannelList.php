<?php

namespace BandwidthLib\Messaging\Models;

class MultiChannelList implements \JsonSerializable
{
    /**
     * @throws \Exception when more than 4 items are added
     */
    public function __construct(
        /** @var array<\JsonSerializable> $items */
        protected array $items = [],
    ) {
        if ($this->count() > 4) {
            $this->throwTooManyItemsException();
        }
    }

    private function throwTooManyItemsException(): void
    {
        throw new \Exception("Multi-channel list cannot have over 4 items");
    }

    /**
     * @throws \Exception when attempting to add a 5th item
     */
    public function addItem(\JsonSerializable $item): void
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
        return $this->count() >= 4;
    }

    public function jsonSerialize(): array
    {
        if ($this->isEmpty()) {
            throw new \Exception("Multi-channel list cannot be empty");
        }

        return $this->items;
    }
}
