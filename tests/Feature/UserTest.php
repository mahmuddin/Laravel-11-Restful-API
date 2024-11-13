<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UserSeeder;
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

    public function testLoginSuccess()
    {
        $this->seed(UserSeeder::class);
        $response = $this->post('/api/users/login', [
            'username' => 'test',
            'password' => 'test',
        ]);
        $response->assertStatus(200)
            ->assertJson([
                "data" => [
                    'username' => 'test',
                    'name' => 'Test',
                ]
            ]);

        $user = User::where('username', operator: 'test')->first();
        self::assertNotNull('test', $user->token);
    }

    public function testLoginFailedUsernameNotFound()
    {
        $response = $this->post('/api/users/login', [
            'username' => 'john_doe',
            'password' => 'password',
        ]);
        $response->assertStatus(401)
            ->assertJson([
                "errors" => [
                    'message' => ['Username or password wrong.']
                ]
            ]);
    }

    public function testLoginFailedPasswordWrong()
    {
        $this->seed(UserSeeder::class);
        $response = $this->post('/api/users/login', [
            'username' => 'test',
            'password' => 'salah',
        ]);
        $response->assertStatus(401)
            ->assertJson([
                "errors" => [
                    'message' => ['Username or password wrong.']
                ]
            ]);
    }
}
