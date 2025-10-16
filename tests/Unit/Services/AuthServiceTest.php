<?php

namespace Tests\Unit\Services;

use App\Contracts\AuthServiceInterface;
use App\DTOs\LoginDTO;
use App\DTOs\RegisterDTO;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    use RefreshDatabase;

    private AuthServiceInterface $authService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authService = app(AuthServiceInterface::class);
    }

    public function test_register_customer_successfully(): void
    {
        $registerDTO = new RegisterDTO(
            name: 'John Doe',
            email: 'john@example.com',
            password: 'password123'
        );
        $result = $this->authService->register($registerDTO);

        $this->assertEquals('Customer created successfully', $result->message);
        $this->assertNotNull($result->customer);
        
        $this->assertDatabaseHas('customers', [
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]);
    }

    public function test_login_customer_successfully(): void
    {
        $customer = Customer::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('password123')
        ]);

        $loginDTO = new LoginDTO(
            email: 'john@example.com',
            password: 'password123'
        );

        $result = $this->authService->login($loginDTO);

        $this->assertEquals('Login successful', $result->message);
        $this->assertNotNull($result->customer);
        $this->assertNotNull($result->access_token);
        $this->assertEquals('Bearer', $result->token_type);
    }

    public function test_login_with_invalid_credentials_throws_exception(): void
    {
        $loginDTO = new LoginDTO(
            email: 'invalid@example.com',
            password: 'wrongpassword'
        );

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $this->authService->login($loginDTO);
    }

    public function test_logout_customer_successfully(): void
    {
        $customer = Customer::factory()->create();
        Sanctum::actingAs($customer, ['*']);
        $result = $this->authService->logout();

        $this->assertEquals('Logout successful', $result->message);
        $this->assertCount(0, $customer->tokens);
    }

    public function test_logout_without_authenticated_customer(): void
    {
        $result = $this->authService->logout();
        $this->assertEquals('Logout successful', $result->message);
    }

}