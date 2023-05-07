<?php

namespace Tests\Support;

use Tests\TestCase;
use Tail\Support\Env;

class EnvTest extends TestCase
{
    public function test_get_env_uses_static_env_value()
    {
        putenv('PATH=custom');
        $this->assertNotNull(Env::get('PATH'));
        $this->assertNotEquals('custom', Env::get('PATH'));
    }

    public function test_uses_get_env_as_a_backup()
    {
        putenv('TAIL_CUSTOM=foobar');
        $this->assertSame('foobar', Env::get('TAIL_CUSTOM'));
    }

    public function test_converts_true_false_into_booleans()
    {
        putenv('TAIL_TRUE=true');
        putenv('TAIL_FALSE=false');

        $this->assertTrue(Env::get('TAIL_TRUE'));
        $this->assertFalse(Env::get('TAIL_FALSE'));
    }

    public function test_missing_key_returns_null()
    {
        $this->assertNull(Env::get('TAIL_MISSING'));
    }
}
