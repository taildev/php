<?php

namespace Tests\Apm\Meta;

use Tests\TestCase;
use Tail\Apm\Meta\User;

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

    public function test_output_to_array()
    {
        $user = new User();
        $user->setId('foo-123');
        $user->setEmail('user@example.com');

        $expect = [
            'id' => 'foo-123',
            'email' => 'user@example.com',
        ];

        $this->assertSame($expect, $user->toArray());
    }
}
