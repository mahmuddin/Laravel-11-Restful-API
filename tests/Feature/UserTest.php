<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function testRegisterSuccess()
    {
        $this->post('/api/users', [
            'username' => 'john_doe',
            'password' => 'password',
            'name' => 'John Doe',
        ])
            ->assertStatus(201)
            ->assertJson([
                "data" => [
                    'username' => 'john_doe',
                    'name' => 'John Doe',
                ]
            ]);
    }

    public function testRegisterFailed()
    {
        $response = $this->post('/users', [
            'name' => 'John Doe',
            'email' => 'dQyv3@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200);
    }

    public function testRegisterUsernameAlreadyExists()
    {
        $response = $this->post('/users', [
            'name' => 'John Doe',
            'email' => 'dQyv3@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200);
    }
}
