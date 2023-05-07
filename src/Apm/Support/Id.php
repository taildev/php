<?php

namespace Tail\Apm\Support;

use Ramsey\Uuid\Uuid;

class Id
{
    /**
     * Generate a new unique ID
     */
    public static function generate(): string
    {
        return Uuid::uuid4()->toString();
    }
}
