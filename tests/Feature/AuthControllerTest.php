<?php

namespace Tests\Feature;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_customer(): void
    {
        $payload = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/auth/register', $payload);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'customer' => ['id', 'name', 'email'],
            ])
            ->assertJson([
                'message' => 'Customer created successfully',
            ]);
    }

    public function test_login_customer(): void
    {
        $customer = Customer::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123')
        ]);

        $payload = [
            'email' => 'test@example.com',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/auth/login', $payload);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'access_token',
                'token_type',
                'customer'
            ])
            ->assertJson([
                'message' => 'Login successful',
                'token_type' => 'Bearer'
            ]);
    }

    public function test_logout_customer(): void
    {
        $customer = $this->createAuthenticatedCustomer();
        $token = $this->createCustomerToken($customer);

        $response = $this->postJson('/api/auth/logout', [], [
            'Authorization' => 'Bearer ' . $token
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Logout successful'
            ]);
    }
}