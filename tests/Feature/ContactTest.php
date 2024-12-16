<?php

namespace Tests\Feature;

use App\Models\Contact;
use Database\Seeders\ContactSeeder;
use Database\Seeders\SearchSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class ContactTest extends TestCase
{
    public function testCreateSuccess()
    {
        $this->seed(UserSeeder::class);
        $response = $this->post(
            '/api/contacts',
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john_doe@mail.com',
                'phone' => '08123456789',
            ],
            [
                'Authorization' => 'test'
            ]
        );
        $response->assertStatus(201)
            ->assertJson([
                "data" => [
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'email' => 'john_doe@mail.com',
                    'phone' => '08123456789',
                ]
            ]);
    }

    public function testCreateFailed()
    {
        $this->seed(UserSeeder::class);
        $response = $this->post(
            '/api/contacts',
            [
                'first_name' => '',
                'last_name' => 'Doe',
                'email' => 'john_doe',
                'phone' => '08123456789',
            ],
            [
                'Authorization' => 'test'
            ]
        );
        $response->assertStatus(400)
            ->assertJson([
                "errors" => [
                    'first_name' => ['The first name field is required.'],
                    'email' => ['The email field must be a valid email address.'],
                ]
            ]);
    }

    public function testCreateUnauthorized()
    {
        $this->seed(UserSeeder::class);
        $response = $this->post(
            '/api/contacts',
            [
                'first_name' => '',
                'last_name' => 'Doe',
                'email' => 'john_doe',
                'phone' => '08123456789',
            ],
            [
                'Authorization' => 'salah'
            ]
        );
        $response->assertStatus(401)
            ->assertJson([
                "errors" => [
                    'message' => ['Unauthorized']
                ]
            ]);
    }

    public function testGetSuccess()
    {
        $this->seed([
            UserSeeder::class,
            ContactSeeder::class
        ]);

        $contact = Contact::query()->limit(1)->first();
        $response = $this->get('/api/contacts/' . $contact->id, [
            'Authorization' => 'test'
        ]);
        $response->assertStatus(200)
            ->assertJson([
                "data" => [
                    'first_name' => 'Test',
                    'last_name' => 'User',
                    'email' => 'test_user@mail.com',
                    'phone' => '08987654321',
                ]
            ]);
    }

    public function testGetNotFound()
    {
        $this->seed([
            UserSeeder::class,
            ContactSeeder::class
        ]);

        $contact = Contact::query()->limit(1)->first();
        $response = $this->get('/api/contacts/' . ($contact->id + 1), [
            'Authorization' => 'test'
        ]);
        $response->assertStatus(404)
            ->assertJson([
                "errors" => [
                    'message' => ['not found']
                ]
            ]);
    }

    public function testGetOtherUserContact()
    {
        $this->seed([
            UserSeeder::class,
            ContactSeeder::class
        ]);

        $contact = Contact::query()->limit(1)->first();
        $response = $this->get('/api/contacts/' . $contact->id, [
            'Authorization' => 'test2'
        ]);
        $response->assertStatus(404)
            ->assertJson([
                "errors" => [
                    'message' => ['not found']
                ]
            ]);
    }

    public function testUpdateSuccess()
    {
        $this->seed([
            UserSeeder::class,
            ContactSeeder::class
        ]);

        $contact = Contact::query()->limit(1)->first();
        $response = $this->put('/api/contacts/' . $contact->id, [
            'first_name' => 'Test2',
            'last_name' => 'User2',
            'email' => 'test_user2@mail.com',
            'phone' => '08987654322',
        ], [
            'Authorization' => 'test'
        ]);
        $response->assertStatus(200)
            ->assertJson([
                "data" => [
                    'first_name' => 'Test2',
                    'last_name' => 'User2',
                    'email' => 'test_user2@mail.com',
                    'phone' => '08987654322',
                ]
            ]);
    }

    public function testUpdateValidationError()
    {
        $this->seed([
            UserSeeder::class,
            ContactSeeder::class
        ]);

        $contact = Contact::query()->limit(1)->first();
        $response = $this->put('/api/contacts/' . $contact->id, [
            'first_name' => '',
            'last_name' => 'User2',
            'email' => 'test_user2',
            'phone' => '08987654322',
        ], [
            'Authorization' => 'test'
        ]);
        $response->assertStatus(400)
            ->assertJson([
                "errors" => [
                    'first_name' => ['The first name field is required.'],
                    'email' => ['The email field must be a valid email address.'],
                ]
            ]);
    }

    public function testDeleteSuccess()
    {
        $this->seed([
            UserSeeder::class,
            ContactSeeder::class
        ]);

        $contact = Contact::query()->limit(1)->first();
        $response = $this->delete('/api/contacts/' . $contact->id, [], [
            'Authorization' => 'test'
        ]);
        $response->assertStatus(200)
            ->assertJson([
                "data" => true
            ]);
    }

    public function testDeleteNotFound()
    {
        $this->seed([
            UserSeeder::class,
            ContactSeeder::class
        ]);

        $contact = Contact::query()->limit(1)->first();
        $response = $this->delete('/api/contacts/' . ($contact->id + 1), [], [
            'Authorization' => 'test'
        ]);
        $response->assertStatus(404)
            ->assertJson([
                "errors" => [
                    'message' => ['not found']
                ]
            ]);
    }

    public function testSearchByFirstName()
    {
        $this->seed([
            UserSeeder::class,
            SearchSeeder::class
        ]);

        $response = $this->get('/api/contacts?name=first', [
            'Authorization' => 'test'
        ]);

        $response->assertStatus(200);

        // Retrieve and log response as array
        $responseData = $response->json();

        // Log and verify response structure
        Log::info(json_encode($responseData, JSON_PRETTY_PRINT));

        // Assert values
        self::assertEquals(10, count($responseData['data']));
        self::assertEquals(20, $responseData['pagination']['total']);
    }
    public function testSearchByEmail()
    {
        $this->seed([
            UserSeeder::class,
            SearchSeeder::class
        ]);

        $response = $this->get('/api/contacts?email=test', [
            'Authorization' => 'test'
        ]);

        $response->assertStatus(200);

        // Retrieve and log response as array
        $responseData = $response->json();

        // Log and verify response structure
        Log::info(json_encode($responseData, JSON_PRETTY_PRINT));

        // Assert values
        self::assertEquals(10, count($responseData['data']));
        self::assertEquals(20, $responseData['pagination']['total']);
    }
    public function testSearchByPhone()
    {
        $this->seed([
            UserSeeder::class,
            SearchSeeder::class
        ]);

        $response = $this->get('/api/contacts?phone=089876543', [
            'Authorization' => 'test'
        ]);

        $response->assertStatus(200);

        // Retrieve and log response as array
        $responseData = $response->json();

        // Log and verify response structure
        Log::info(json_encode($responseData, JSON_PRETTY_PRINT));

        // Assert values
        self::assertEquals(10, count($responseData['data']));
        self::assertEquals(20, $responseData['pagination']['total']);
    }

    public function testSearchNotFound()
    {
        $this->seed([
            UserSeeder::class,
            SearchSeeder::class
        ]);

        $response = $this->get('/api/contacts?name=tidakada', [
            'Authorization' => 'test'
        ]);

        $response->assertStatus(200);

        // Retrieve and log response as array
        $responseData = $response->json();

        // Log and verify response structure
        Log::info(json_encode($responseData, JSON_PRETTY_PRINT));

        // Assert values
        self::assertEquals(0, count($responseData['data']));
        self::assertEquals(0, $responseData['pagination']['total']);
    }
    public function testSearchWithPage()
    {
        $this->seed([
            UserSeeder::class,
            SearchSeeder::class
        ]);

        $response = $this->get('/api/contacts?size=5&page=2', [
            'Authorization' => 'test'
        ]);

        $response->assertStatus(200);

        // Retrieve and log response as array
        $responseData = $response->json();

        // Log and verify response structure
        Log::info(json_encode($responseData, JSON_PRETTY_PRINT));

        // Assert values
        self::assertEquals(5, count($responseData['data']));
        self::assertEquals(20, $responseData['pagination']['total']);
        self::assertEquals(2, $responseData['pagination']['current_page']);
    }
}
