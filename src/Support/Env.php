<?php

namespace Tail\Support;

class Env
{

    public static function get($key)
    {
        $value = null;

        if (array_key_exists($key, $_SERVER)) {
            $value = $_SERVER[$key];
        }

        if ($value === null && getenv($key) !== false) {
            $value = getenv($key);
        }

        if ($value === 'true') {
            $value = true;
        }

        if ($value === 'false') {
            $value = false;
        }

        return $value;
    }
}
