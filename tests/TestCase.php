<?php

namespace Tests;

use App\Models\Customer;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;
use Laravel\Sanctum\Sanctum;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Executar migrations do teste
        Artisan::call('migrate:fresh');
    }

    protected function createAuthenticatedCustomer(array $attributes = []): Customer
    {
        $customer = Customer::factory()->create($attributes);
        
        // Usar Sanctum para autenticação nos testes
        Sanctum::actingAs($customer, ['*']);
        
        return $customer;
    }

    protected function createCustomerToken(Customer $customer): string
    {
        return $customer->createToken('test-token')->plainTextToken;
    }
}