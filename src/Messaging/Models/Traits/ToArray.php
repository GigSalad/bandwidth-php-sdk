<?php

namespace BandwidthLib\Messaging\Models\Traits;

trait ToArray
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
