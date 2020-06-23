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
            'operation' => 'custom-operation',
            'query' => 'custom-query',
        ]);

        $this->assertSame('custom-name', $database->name());
        $this->assertSame('custom-operation', $database->operation());
        $this->assertSame('custom-query', $database->query());
    }

    public function test_set_name()
    {
        $result = $this->database->setName('mysql');
        $this->assertSame($this->database, $result);
        $this->assertSame('mysql', $this->database->name());
    }

    public function test_set_operation()
    {
        $result = $this->database->setOperation('read');
        $this->assertSame($this->database, $result);
        $this->assertSame('read', $this->database->operation());
    }

    public function test_is_read_operation()
    {
        $result = $this->database->isReadOperation();
        $this->assertSame($this->database, $result);
        $this->assertSame(Database::READ_OPERATION, $this->database->operation());
    }

    public function test_is_write_operation()
    {
        $result = $this->database->isWriteOperation();
        $this->assertSame($this->database, $result);
        $this->assertSame(Database::WRITE_OPERATION, $this->database->operation());
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
        $database->setOperation('read');
        $database->setQuery('select *');

        $expect = [
            'name' => 'mysql',
            'operation' => 'read',
            'query' => 'select *',
        ];

        $this->assertSame($expect, $database->toArray());
    }
}
