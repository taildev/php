<?php

namespace Tests\Apm\Meta;

use Tests\TestCase;
use Tail\Apm\Meta\Database;

class DatabaseTest extends TestCase
{

    /** @var Database */
    protected $database;

    public function setUp(): void
    {
        parent::setUp();
        $this->database = new Database();
    }

    public function test_fill_from_array()
    {
        $database = new Database();
        $database->fillFromArray([
            'name' => 'custom-name',
            'query' => 'custom-query',
        ]);

        $this->assertSame('custom-name', $database->name());
        $this->assertSame('custom-query', $database->query());
    }

    public function test_set_name()
    {
        $result = $this->database->setName('mysql');
        $this->assertSame($this->database, $result);
        $this->assertSame('mysql', $this->database->name());
    }

    public function test_set_query()
    {
        $result = $this->database->setQuery('select *');
        $this->assertSame($this->database, $result);
        $this->assertSame('select *', $this->database->query());
    }

    public function test_output_to_array()
    {
        $database = new Database();
        $database->setName('mysql');
        $database->setQuery('select *');

        $expect = [
            'name' => 'mysql',
            'query' => 'select *',
        ];

        $this->assertSame($expect, $database->toArray());
    }
}
