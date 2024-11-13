<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function testRegisterSuccess()
    {
        $response = $this->post('/api/users', [
            'username' => 'john_doe',
            'password' => 'password',
            'name' => 'John Doe',
        ]);
        $response->assertStatus(201)
            ->assertJson([
                "data" => [
                    'username' => 'john_doe',
                    'name' => 'John Doe',
                ]
            ]);
    }

    public function testRegisterFailed()
    {
        $response = $this->post('/api/users', [
            'usename' => '',
            'password' => '',
            'name' => '',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                "errors" => [
                    'username' => ['The username field is required.'],
                    "password" => ['The password field is required.'],
                    "name" => ['The name field is required.']
                ]
            ]);
    }

    public function testRegisterUsernameAlreadyExists()
    {
        $this->testRegisterSuccess();
        $response = $this->post('/api/users', [
            'username' => 'john_doe',
            'password' => 'password',
            'name' => 'John Doe',
        ]);
        $response->assertStatus(400)
            ->assertJson([
                "errors" => [
                    'username' => ['The username has already been taken.']
                ]
            ]);
    }
}
