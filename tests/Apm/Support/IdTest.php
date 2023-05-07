<?php

namespace Tests\Apm\Support;

use Tests\TestCase;
use Tail\Apm\Support\Id;

class IdTest extends TestCase
{
    public function test_generate_new_id()
    {
        $id1 = Id::generate();
        $id2 = Id::generate();

        $this->assertNotEmpty($id1);
        $this->assertNotEmpty($id2);
        $this->assertNotSame($id1, $id2);
    }
}
