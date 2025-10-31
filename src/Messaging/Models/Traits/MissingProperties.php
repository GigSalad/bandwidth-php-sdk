<?php

namespace BandwidthLib\Messaging\Models\Traits;

trait MissingProperties
{
    /**
     * Obtain the names of this instance's properties that are missing.
     *
     * @return string[]
     */
    public function missingProperties(): array
    {
        return array_keys(
            array_filter(get_object_vars($this), fn($value) => empty($value)),
        );
    }

    public function hasMissingProperties(): bool
    {
        return !empty($this->missingProperties());
    }

    public function throwIfMissingProperties(): void
    {
        if ($this->hasMissingProperties()) {
            $className = static::class;

            throw new \Exception(
                "Missing value(s) from class '{$className}': " .
                    implode(", ", $this->missingProperties()),
            );
        }
    }
}
