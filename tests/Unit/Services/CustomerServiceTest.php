<?php

namespace Tests\Unit\Services;

use App\Contracts\CustomerServiceInterface;
use App\Contracts\FavoriteServiceInterface;
use App\Contracts\ProductServiceInterface;
use App\DTOs\CustomerResponseDTO;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Mockery;
use Tests\TestCase;

class CustomerServiceTest extends TestCase
{
    use RefreshDatabase;

    private function createCustomerServiceWithMocks(array $favoriteServiceConfig = []): CustomerServiceInterface
    {
        $productServiceMock = Mockery::mock(ProductServiceInterface::class);
        $favoriteServiceMock = Mockery::mock(FavoriteServiceInterface::class);
        
        $productServiceMock->shouldReceive('getProductById')->andReturn(null);
        $productServiceMock->shouldReceive('getProductsByIds')->andReturn(collect());
        $productServiceMock->shouldReceive('getAllProducts')->andReturn(collect());
        $productServiceMock->shouldReceive('getProductsByCategory')->andReturn(collect());
        $productServiceMock->shouldReceive('searchProducts')->andReturn(collect());
        
        $favoriteServiceMock->shouldReceive('addToFavorites')->andReturn(
            new \App\DTOs\FavoriteResponseDTO('Produto adicionado aos favoritos.')
        );
        $favoriteServiceMock->shouldReceive('removeFromFavorites')->andReturn(
            new \App\DTOs\FavoriteResponseDTO('Produto removido dos favoritos.')
        );
        $favoriteServiceMock->shouldReceive('isProductInFavorites')->andReturn(false);
        $favoriteServiceMock->shouldReceive('getFavoriteById')->andReturn(null);
        
        foreach ($favoriteServiceConfig as $method => $config) {
            $expectation = $favoriteServiceMock->shouldReceive($method)
                ->times($config['times'] ?? 1);
            
            if (isset($config['with'])) {
                $expectation->with(...$config['with']);
            } else {
                $expectation->withAnyArgs();
            }
            
            if (is_callable($config['return'])) {
                $expectation->andReturnUsing($config['return']);
            } else {
                $expectation->andReturn($config['return']);
            }
        }
        
        if (!isset($favoriteServiceConfig['getCustomerFavorites'])) {
            $favoriteServiceMock->shouldReceive('getCustomerFavorites')->andReturn(collect());
        }
        
        app()->instance(ProductServiceInterface::class, $productServiceMock);
        app()->instance(FavoriteServiceInterface::class, $favoriteServiceMock);
        
        return app(CustomerServiceInterface::class);
    }

    public function test_get_profile_with_favorites(): void
    {
        $customer = $this->createAuthenticatedCustomer();
        
        $customerService = $this->createCustomerServiceWithMocks([
            'getCustomerFavorites' => [
                'with' => [$customer->id],
                'return' => collect([
                    [
                        'favorite_id' => 1, 
                        'added_at' => now()->toDateTimeString(),
                        'product' => [
                            'id' => 1, 
                            'title' => 'Product 1',
                            'price' => 19.99,
                            'category' => 'electronics',
                            'image' => 'image1.jpg',
                            'rating' => ['rate' => 4.5, 'count' => 100]
                        ]
                    ]
                ])
            ]
        ]);

        $result = $customerService->getProfile();

        $this->assertIsArray($result);
        $this->assertEquals($customer->id, $result['customer']['id']);
        $this->assertCount(1, $result['favorites'], 'Favorites array should have 1 item');
        $this->assertEquals('Product 1', $result['favorites'][0]['product']['title']);
    }

    public function test_get_all_customers_with_favorites_count(): void
    {
        $customer1 = Customer::factory()->create();
        $customer2 = Customer::factory()->create();
        
        Sanctum::actingAs($customer1);

        $customerService = $this->createCustomerServiceWithMocks([
            'getCustomerFavorites' => [
                'times' => 2,
                'with' => [Mockery::any()],
                'return' => function($customerId) use ($customer1) {
                    return $customerId === $customer1->id ? collect([1, 2]) : collect();
                }
            ]
        ]);

        $result = $customerService->getAllCustomers();

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        
        $customer1Data = collect($result)->firstWhere('id', $customer1->id);
        $customer2Data = collect($result)->firstWhere('id', $customer2->id);
        
        $this->assertEquals(2, $customer1Data['favorites_count']);
        $this->assertEquals(0, $customer2Data['favorites_count']);
    }
    
    public function test_delete_customer_successfully(): void
    {
        $currentCustomer = $this->createAuthenticatedCustomer();
        $customerToDelete = Customer::factory()->create(['name' => 'John Doe']);

        $customerService = $this->createCustomerServiceWithMocks([
            'getCustomerFavorites' => [
                'times' => 2,
                'with' => [$customerToDelete->id],
                'return' => collect()
            ]
        ]);

        $result = $customerService->deleteCustomer($customerToDelete->id);

        $this->assertInstanceOf(CustomerResponseDTO::class, $result);
        $this->assertEquals("Customer 'John Doe' successfully deleted. 0 favorites removed.", $result->message);
        
        $deletedCustomer = Customer::withTrashed()->find($customerToDelete->id);
        $this->assertNotNull($deletedCustomer);
        $this->assertNotNull($deletedCustomer->deleted_at);
        $this->assertNull(Customer::find($customerToDelete->id));
    }

    public function test_cannot_delete_own_account(): void
    {
        $currentCustomer = $this->createAuthenticatedCustomer();
        $customerService = $this->createCustomerServiceWithMocks();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('You cannot delete your own account.');

        $customerService->deleteCustomer($currentCustomer->id);
    }

    public function test_get_profile_returns_authenticated_customer(): void
    {
        $customer = $this->createAuthenticatedCustomer();
        $customerService = $this->createCustomerServiceWithMocks();

        $result = $customerService->getProfile();

        $this->assertIsArray($result);
        $this->assertEquals($customer->id, $result['customer']['id']);
        $this->assertEquals($customer->name, $result['customer']['name']);
        $this->assertEquals($customer->email, $result['customer']['email']);
    }

    public function test_update_profile_successfully(): void
    {
        $customer = $this->createAuthenticatedCustomer();
        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com'
        ];
        $customerService = $this->createCustomerServiceWithMocks();

        $result = $customerService->updateProfile($updateData);

        $this->assertInstanceOf(Customer::class, $result);
        $this->assertEquals('Updated Name', $result->name);
        $this->assertEquals('updated@example.com', $result->email);
        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com'
        ]);
    }

    public function test_get_profile_with_favorites_throws_exception_when_not_authenticated(): void
    {
        auth()->logout();
        $customerService = $this->createCustomerServiceWithMocks();
        $this->expectException(\Throwable::class);
        
        $this->expectExceptionMessageMatches('/(User not authenticated.|Attempt to read property)/');

        $customerService->getProfile();
    }

    public function test_update_profile_throws_exception_when_not_authenticated(): void
    {
        auth()->logout();
        $customerService = $this->createCustomerServiceWithMocks();

        $this->expectException(\Throwable::class);
        $this->expectExceptionMessageMatches('/(User not authenticated.|Call to a member function update\(\) on null)/');

        $customerService->updateProfile(['name' => 'Test']);
    }
}