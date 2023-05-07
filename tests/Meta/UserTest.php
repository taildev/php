<?php

namespace Tests\Meta;

use stdClass;
use Tests\TestCase;
use Tail\Meta\User;

class UserTest extends TestCase
{
    protected $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = new User();
    }

    public function test_fill_from_array()
    {
        $user = new User();
        $user->fillFromArray([
            'id' => 'custom-id',
            'email' => 'custom-email',
        ]);

        $this->assertSame('custom-id', $user->id());
        $this->assertSame('custom-email', $user->email());
    }

    public function test_set_id()
    {
        $result = $this->user->setId('foo-123');
        $this->assertSame($this->user, $result);
        $this->assertSame('foo-123', $this->user->id());
    }

    public function test_set_email()
    {
        $result = $this->user->setEmail('user@example.com');
        $this->assertSame($this->user, $result);
        $this->assertSame('user@example.com', $this->user->email());
    }

    public function test_merge()
    {
        $user = new User();
        $user->setId('1');
        $user->setEmail('1@1.com');

        $user->merge(['email' => '2@2.com']);

        $this->assertSame(['id' => '1', 'email' => '2@2.com'], $user->serialize());
    }

    public function test_serialize()
    {
        $user = new User();
        $user->setId('foo-123');
        $user->setEmail('user@example.com');

        $expect = [
            'id' => 'foo-123',
            'email' => 'user@example.com',
        ];

        $this->assertSame($expect, $user->serialize());
    }

    public function test_serialize_partial()
    {
        $user = new User();
        $user->setEmail('user@example.com');

        $expect = [
            'email' => 'user@example.com',
        ];

        $this->assertSame($expect, $user->serialize());
    }

    public function test_serialize_empty()
    {
        $user = new User();

        $expect = new stdClass();

        $this->assertEquals($expect, $user->serialize());
    }
}
