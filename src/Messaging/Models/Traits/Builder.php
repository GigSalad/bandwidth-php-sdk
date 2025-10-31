<?php

namespace BandwidthLib\Messaging\Models\Traits;

trait Builder
{
    /**
     * A static "factory" method that creates a new instance of the
     * class with no constructor arguments, so it will be "empty" and
     * likely needs further building via fluent methods.
     *
     * @return static A new instance of the class.
     */
    public static function build(): static
    {
        return new static();
    }

    /**
     * A static "constructor" method that creates a new instance of the
     * class by forwarding all arguments to the class constructor.
     */
    public static function new(mixed ...$arguments): static
    {
        return new static(...$arguments);
    }
}
