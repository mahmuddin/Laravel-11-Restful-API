<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Tests\TestCase;
use Illuminate\Support\Facades\Log;

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

    public function testGetSuccess()
    {
        $this->seed(UserSeeder::class);
        $response = $this->get('/api/users/current', [
            'Authorization' => 'test'
        ]);
        $response->assertStatus(200)
            ->assertJson([
                "data" => [
                    'username' => 'test',
                    'name' => 'Test',
                ]
            ]);
    }

    public function testGetUnauthorized()
    {
        $this->seed(UserSeeder::class);
        $response = $this->get('/api/users/current');
        $response->assertStatus(401)
            ->assertJson([
                "errors" => [
                    'message' => ['Unauthorized']
                ]
            ]);
    }

    public function testGetInvalidToken()
    {
        $this->seed(UserSeeder::class);
        $response = $this->get('/api/users/current', [
            'Authorization' => 'salah'
        ]);
        $response->assertStatus(401)
            ->assertJson([
                "errors" => [
                    'message' => ['Unauthorized']
                ]
            ]);
    }

    public function testUpdateNameSuccess()
    {
        $this->seed(UserSeeder::class);
        $oldUser = User::where('username', 'test')->first();
        $response = $this->patch('/api/users/current', [
            'name' => 'baru',
        ], [
            'Authorization' => 'test'
        ]);
        $response->assertStatus(200)
            ->assertJson([
                "data" => [
                    'username' => 'test',
                    'name' => 'baru',
                ]
            ]);

        $newUser = User::where('username', 'test')->first();
        self::assertNotEquals($oldUser->name, $newUser->name);
    }

    public function testUpdatePasswordSuccess()
    {
        $this->seed(UserSeeder::class);
        $oldUser = User::where('username', 'test')->first();
        $response = $this->patch('/api/users/current', [
            'password' => 'baru',
        ], [
            'Authorization' => 'test'
        ]);
        $response->assertStatus(200)
            ->assertJson([
                "data" => [
                    'username' => 'test',
                    'name' => 'Test',
                ]
            ]);

        $newUser = User::where('username', 'test')->first();
        self::assertNotEquals($oldUser->password, $newUser->password);
    }

    public function testUpdateFailed()
    {
        $this->seed(UserSeeder::class);
        $response = $this->patch('/api/users/current', [
            'name' => Str::random(101),
        ], [
            'Authorization' => 'test'
        ]);
        $response->assertStatus(400)
            ->assertJson([
                "errors" => [
                    'name' => ['The name field must not be greater than 100 characters.']
                ]
            ]);
    }

    public function testLogoutSuccess()
    {
        $this->seed(UserSeeder::class);
        $response = $this->delete(uri: '/api/users/logout', headers: [
            'Authorization' => 'test'
        ]);
        $response->assertStatus(200)
            ->assertJson([
                "data" => true
            ]);

        $user = User::where('username', 'test')->first();
        self::assertNull($user->token);
    }

    public function testLogoutFailed()
    {
        $this->seed(UserSeeder::class);
        $response = $this->delete(uri: '/api/users/logout', headers: [
            'Authorization' => 'salah'
        ]);
        $response->assertStatus(401)
            ->assertJson([
                "errors" => [
                    'message' => ['Unauthorized']
                ]
            ]);
    }
}
