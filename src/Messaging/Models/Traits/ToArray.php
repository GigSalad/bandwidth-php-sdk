<?php

namespace BandwidthLib\Messaging\Models\Traits;

use Exception;

trait ToArray
{
    /**
     * There is no validation by default but classes using this trait
     * can provide their own implementation to throw errors or handle
     * validation in any way.
     *
     * This is currently entwined with `toArray()` but it's public and
     * might serve other purposes or could become a separate trait...
     *
     * @throws Exception
     */
    public function validate(): void {}

    /**
     * @return mixed[]
     */
    public function toArray(): array
    {
        $this->validate();

        // We shouldn't remove `0` or `[]` values, but we don't want
        // `null` or empty strings, which Bandwidth's API rejects with
        // HTTP errors like "400 Request is malformed or invalid."
        return array_filter(
            array_map(static::toArrayValue(...), get_object_vars($this)),
            fn($value) => !is_null($value) &&
                (!is_string($value) || trim($value) !== ""),
        );
    }

    protected static function toArrayValue(mixed $value): mixed
    {
        $into = fn($item) => is_object($item) && method_exists($item, "toArray")
            ? $item->toArray()
            : $item;

        return is_array($value) ? array_map($into, $value) : $into($value);
    }
}
