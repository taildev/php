<?php

namespace Tests\Meta;

use stdClass;
use Tests\TestCase;
use Tail\Meta\Tags;

class TagsTest extends TestCase
{
    public function test_get_and_set_tags()
    {
        $tags = new Tags(['foo' => 'bar']);
        $tags->set('foo', 'updated-bar')
            ->set('number', 1.234);
        $tags->set('number', '2.345');

        $this->assertSame('updated-bar', $tags->get('foo'));
        $this->assertSame('2.345', $tags->get('number'));
    }

    public function test_replace_all_tags()
    {
        $tags = new Tags();
        $tags->set('one', '1');
        $tags->set('two', '2');

        $new = [
            'three' => '3',
            'four' => '4',
        ];
        $tags->replaceAll($new);

        $this->assertNull($tags->get('one'));
        $this->assertNull($tags->get('two'));
        $this->assertSame($new, $tags->all());
    }

    public function test_merge()
    {
        $tags = new Tags();
        $tags->set('k1', 'v1');
        $tags->set('k2', 'v2');
        $tags->merge(['k1' => 'vv11', 'k3' => 'v3']);
        $this->assertSame($tags->all(), [
            'k1' => 'vv11',
            'k2' => 'v2',
            'k3' => 'v3',
        ]);
    }

    public function test_serialize()
    {
        $data = [
            'foo' => 'bar',
            'number' => 1.234,
            'bool' => true,
        ];
        $tags = new Tags($data);

        $this->assertSame($data, $tags->serialize());
    }

    public function test_serialize_empty()
    {
        $tags = new Tags([]);

        $expect = new stdClass();
        $this->assertEquals($expect, $tags->serialize());
    }
}
