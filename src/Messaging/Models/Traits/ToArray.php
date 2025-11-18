<?php

namespace BandwidthLib\Messaging\Models\Traits;

trait ToArray
{
    /**
     * @return mixed[]
     */
    public function toArray(): array
    {
        return array_map(static::toArrayValue(...), get_object_vars($this));
    }

    protected static function toArrayValue(mixed $value): mixed
    {
        $into = fn($item) => is_object($item) && method_exists($item, "toArray")
            ? $item->toArray()
            : $item;

        return is_array($value) ? array_map($into, $value) : $into($value);
    }
}
