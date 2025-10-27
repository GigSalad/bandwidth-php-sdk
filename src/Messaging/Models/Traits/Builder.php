<?php

namespace BandwidthLib\Messaging\Models\Traits;

trait Builder
{
    /**
     * A static "factory" method that creates a new instance of the class.
     *
     * @return static A new instance of the class.
     */
    public static function build(): static
    {
        return new static();
    }
}
