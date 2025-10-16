<?php

namespace App\Services;

use App\Contracts\ProductServiceInterface;
use App\DTOs\ProductDTO;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ProductService implements ProductServiceInterface
{
    public function __construct()
    {
        $this->apiUrl = config('services.fake_store.url');
    }

    public function getAllProducts(): Collection
    {
        return Cache::remember('products.all', 3600, function () {
            try {
                $response = Http::timeout(30)->get($this->apiUrl);
                
                if ($response->successful()) {
                    $products = $response->json();
                    
                    return collect($products)->map(function ($productData) {
                        return ProductDTO::fromApiResponse($productData);
                    });
                }
                
                Log::error('Failed to fetch products from API', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                
                return collect();
            } catch (\Exception $e) {
                Log::error('Error fetching products from API', [
                    'error' => $e->getMessage()
                ]);
                return collect();
            }
        });
    }

    public function getProductById(int $id): ?ProductDTO
    {
        return Cache::remember("product.{$id}", 3600, function () use ($id) {
            try {
                $response = Http::timeout(30)->get("{$this->apiUrl}/{$id}");

                Log::info("{$this->apiUrl}/{$id}");
                
                if ($response->successful()) {
                    $productData = $response->json();
                    return ProductDTO::fromApiResponse($productData);
                }
                
                Log::error('Failed to fetch product from API', [
                    'product_id' => $id,
                    'status' => $response->status()
                ]);
                
                return null;
            } catch (\Exception $e) {
                Log::error('Error fetching product from API', [
                    'product_id' => $id,
                    'error' => $e->getMessage()
                ]);
                return null;
            }
        });
    }

    public function getProductsByCategory(string $category): Collection
    {
        return Cache::remember("products.category.{$category}", 3600, function () use ($category) {
            try {
                $response = Http::timeout(30)->get("{$this->apiUrl}/category/{$category}");
                
                if ($response->successful()) {
                    $products = $response->json();
                    
                    return collect($products)->map(function ($productData) {
                        return ProductDTO::fromApiResponse($productData);
                    });
                }
                
                return collect();
            } catch (\Exception $e) {
                Log::error('Error fetching products by category from API', [
                    'category' => $category,
                    'error' => $e->getMessage()
                ]);
                return collect();
            }
        });
    }

    public function searchProducts(string $query): Collection
    {
        $allProducts = $this->getAllProducts();
        
        return $allProducts->filter(function (ProductDTO $product) use ($query) {
            return stripos($product->title, $query) !== false ||
                   stripos($product->description, $query) !== false ||
                   stripos($product->category, $query) !== false;
        });
    }

    public function getProductsByIds(array $ids): Collection
    {
        $allProducts = $this->getAllProducts();
        
        return $allProducts->filter(function (ProductDTO $product) use ($ids) {
            return in_array($product->id, $ids);
        });
    }
}