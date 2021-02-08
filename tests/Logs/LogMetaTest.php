<?php

namespace Tests\Logs;

use Tests\TestCase;
use Tail\Logs\LogMeta;

class LogMetaTest extends TestCase
{

    public function test_output_to_array()
    {
        $meta = new LogMeta();
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
