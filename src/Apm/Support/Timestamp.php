<?php

namespace Tail\Apm\Support;

class Timestamp
{
    /**
     * Current timestamp in milliseconds since epoch
     */
    public static function nowInMs(): int
    {
        return ceil(microtime(true) * 1000);
    }
}
