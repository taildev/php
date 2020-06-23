<?php

namespace Tests\Apm\Meta;

use Tests\TestCase;
use Tail\Apm\Meta\Tags;

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

    public function test_output_to_array()
    {
        $data = [
            'foo' => 'bar',
            'number' => 1.234,
            'bool' => true,
        ];
        $tags = new Tags($data);

        $this->assertSame($data, $tags->toArray());
    }
}
