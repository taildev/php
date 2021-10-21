<?php

namespace Tests;

use Tail\TailMeta;
use Tests\TestCase;

class LogMetaTest extends TestCase
{

    public function test_output_to_array()
    {
        $meta = new TailMeta();
        $expect = [
            'agent' => $meta->agent()->toArray(),
            'service' => $meta->service()->toArray(),
            'system' => $meta->system()->toArray(),
            'tags' => $meta->tags()->toArray(),
            'user' => $meta->user()->toArray(),
        ];

        $this->assertSame($expect, $meta->toArray());
    }
}
