<?php

namespace BandwidthLib\Messaging\Models\Contracts;

use Exception;

interface ArrayConvertible
{
    /**
     * @return mixed[]
     */
    public function toArray(): array;

    /**
     * @throws Exception
     * @param mixed[] $data
     * @return static
     */
    public static function fromArray(array $data): static;

    /**
     * @param mixed[] $data
     * @return null|static
     */
    public static function tryFromArray(array $data): ?static;
}
