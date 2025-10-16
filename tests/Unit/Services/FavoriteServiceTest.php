<?php

namespace Tests\Unit\Services;

use App\Contracts\FavoriteServiceInterface;
use App\Contracts\ProductServiceInterface;
use App\DTOs\FavoriteDTO;
use App\DTOs\ProductDTO;
use App\Models\Customer;
use App\Models\Favorite;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteServiceTest extends TestCase
{
    use RefreshDatabase;

    private FavoriteServiceInterface $favoriteService;
    private $productServiceMock;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->productServiceMock = \Mockery::mock(ProductServiceInterface::class);
        
        $product1 = new ProductDTO(
            id: 1,
            title: 'Test Product',
            description: 'Test Description',
            price: 19.99,
            category: 'electronics',
            image: 'test.jpg',
            rating: ['rate' => 4.5, 'count' => 100]
        );

        $product2 = new ProductDTO(
            id: 2,
            title: 'Test Product 2',
            description: 'Test Description 2',
            price: 29.99,
            category: 'clothing',
            image: 'test2.jpg',
            rating: ['rate' => 4.0, 'count' => 50]
        );

        $product3 = new ProductDTO(
            id: 3,
            title: 'Test Product 3',
            description: 'Test Description 3',
            price: 39.99,
            category: 'home',
            image: 'test3.jpg',
            rating: ['rate' => 3.5, 'count' => 200]
        );

        $this->productServiceMock->shouldReceive('getProductById')
            ->with(1)
            ->andReturn($product1);
            
        $this->productServiceMock->shouldReceive('getProductById')
            ->with(2)
            ->andReturn($product2);
            
        $this->productServiceMock->shouldReceive('getProductById')
            ->with(3)
            ->andReturn($product3);

        $this->productServiceMock->shouldReceive('getProductsByIds')
            ->andReturnUsing(function ($ids) {
                $products = [
                    1 => new ProductDTO(1, 'Product 1', 'Description 1', 19.99, 'electronics', 'image1.jpg', ['rate' => 4.5, 'count' => 100]),
                    2 => new ProductDTO(2, 'Product 2', 'Description 2', 29.99, 'clothing', 'image2.jpg', ['rate' => 4.0, 'count' => 50]),
                    3 => new ProductDTO(3, 'Product 3', 'Description 3', 39.99, 'home', 'image3.jpg', ['rate' => 3.5, 'count' => 200]),
                ];
                
                return collect($ids)->map(function ($id) use ($products) {
                    return $products[$id] ?? null;
                })->filter();
            });

        $this->productServiceMock->shouldReceive('getAllProducts')->andReturn(collect());
        $this->productServiceMock->shouldReceive('getProductsByCategory')->andReturn(collect());
        $this->productServiceMock->shouldReceive('searchProducts')->andReturn(collect());
        $this->productServiceMock->shouldReceive('getProductById')->withAnyArgs()->andReturn(null);

        app()->instance(ProductServiceInterface::class, $this->productServiceMock);
        $this->favoriteService = app(FavoriteServiceInterface::class);
    }

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }

    public function test_add_to_favorites_successfully(): void
    {
        $customer = Customer::factory()->create();
        $productId = 1;
        
        $favoriteDTO = new FavoriteDTO(
            customer_id: $customer->id,
            product_id: $productId
        );

        $result = $this->favoriteService->addToFavorites($favoriteDTO);

        $this->assertEquals("Product 'Test Product' added to favorites.", $result->message);
        $this->assertNotNull($result->favorite);
        $this->assertDatabaseHas('favorites', [
            'customer_id' => $customer->id,
            'product_id' => $productId
        ]);
    }

    public function test_cannot_add_duplicate_favorite(): void
    {
        $customer = Customer::factory()->create();
        $productId = 1;
        
        $favoriteDTO = new FavoriteDTO(
            customer_id: $customer->id,
            product_id: $productId
        );

        $this->favoriteService->addToFavorites($favoriteDTO);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('This product is already in your favorites.');

        $this->favoriteService->addToFavorites($favoriteDTO);
    }

    public function test_remove_from_favorites_successfully(): void
    {
        $customer = Customer::factory()->create();
        $favorite = Favorite::factory()->create([
            'customer_id' => $customer->id,
            'product_id' => 1
        ]);

        $result = $this->favoriteService->removeFromFavorites($favorite->id);

        $this->assertEquals("Product 'Test Product' removed from favorites.", $result->message);
        $this->assertDatabaseMissing('favorites', ['id' => $favorite->id]);
    }

    public function test_get_customer_favorites(): void
    {
        $customer = Customer::factory()->create();
        
        Favorite::factory()->create([
            'customer_id' => $customer->id,
            'product_id' => 1
        ]);
        
        Favorite::factory()->create([
            'customer_id' => $customer->id,
            'product_id' => 2
        ]);

        $result = $this->favoriteService->getCustomerFavorites($customer->id);

        $this->assertCount(2, $result);
        
        $firstFavorite = $result->first();
        $this->assertArrayHasKey('favorite_id', $firstFavorite);
        $this->assertArrayHasKey('added_at', $firstFavorite);
        $this->assertArrayHasKey('product', $firstFavorite);
        $this->assertEquals('Product 1', $firstFavorite['product']['title']);
    }

    public function test_is_product_in_favorites_returns_true_when_exists(): void
    {
        $customer = Customer::factory()->create();
        $productId = 1;
        
        Favorite::factory()->create([
            'customer_id' => $customer->id,
            'product_id' => $productId
        ]);

        $result = $this->favoriteService->isProductInFavorites($customer->id, $productId);

        $this->assertTrue($result);
    }

    public function test_is_product_in_favorites_returns_false_when_not_exists(): void
    {
        $customer = Customer::factory()->create();
        $productId = 999;

        $result = $this->favoriteService->isProductInFavorites($customer->id, $productId);

        $this->assertFalse($result);
    }

    public function test_remove_from_favorites_by_product_id(): void
    {
        $customer = Customer::factory()->create();
        $productId = 1;
        
        $favorite = Favorite::factory()->create([
            'customer_id' => $customer->id,
            'product_id' => $productId
        ]);

        $result = $this->favoriteService->removeFromFavoritesByProductId($customer->id, $productId);

        $this->assertEquals("Product 'Test Product' removed from favorites.", $result->message);
        $this->assertDatabaseMissing('favorites', [
            'customer_id' => $customer->id,
            'product_id' => $productId
        ]);
    }

    public function test_add_to_favorites_throws_exception_when_product_not_found(): void
    {
        $customer = Customer::factory()->create();
        $nonExistentProductId = 999;
        
        $favoriteDTO = new FavoriteDTO(
            customer_id: $customer->id,
            product_id: $nonExistentProductId
        );

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $this->expectExceptionMessage('Product not found in external API.');

        $this->favoriteService->addToFavorites($favoriteDTO);
    }
}