<?php

namespace Tail\Apm\Support;

class Timestamp
{

    /**
     * Current timestamp in milliseconds since epoch
     */
    public static function nowInMs(): float
    {
        return microtime(true) * 1000;
    }
}
