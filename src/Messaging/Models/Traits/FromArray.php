<?php

namespace BandwidthLib\Messaging\Models\Traits;

use Exception;

trait FromArray
{
    /**
     * @param mixed[] $data
     */
    abstract public static function fromArray(array $data): static;

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
}
