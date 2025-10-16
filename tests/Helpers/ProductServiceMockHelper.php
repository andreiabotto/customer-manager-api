<?php

namespace Tests\Unit\Services;

use App\Contracts\ProductServiceInterface;
use App\DTOs\ProductDTO;
use Illuminate\Support\Collection;

class ProductServiceMock implements ProductServiceInterface
{
    public function getAllProducts(): Collection
    {
        return collect([
            $this->createProductDTO(1, 'Product 1', 19.99, 'electronics'),
            $this->createProductDTO(2, 'Product 2', 29.99, 'clothing'),
            $this->createProductDTO(3, 'Product 3', 39.99, 'home'),
        ]);
    }

    public function getProductById(int $id): ?ProductDTO
    {
        $products = [
            1 => $this->createProductDTO(1, 'Product 1', 19.99, 'electronics'),
            2 => $this->createProductDTO(2, 'Product 2', 29.99, 'clothing'),
            3 => $this->createProductDTO(3, 'Product 3', 39.99, 'home'),
        ];

        return $products[$id] ?? null;
    }

    public function getProductsByCategory(string $category): Collection
    {
        return $this->getAllProducts()->filter(function (ProductDTO $product) use ($category) {
            return $product->category === $category;
        });
    }

    public function searchProducts(string $query): Collection
    {
        return $this->getAllProducts()->filter(function (ProductDTO $product) use ($query) {
            return stripos($product->title, $query) !== false ||
                   stripos($product->description, $query) !== false;
        });
    }

    public function getProductsByIds(array $ids): Collection
    {
        return $this->getAllProducts()->filter(function (ProductDTO $product) use ($ids) {
            return in_array($product->id, $ids);
        });
    }

    private function createProductDTO(int $id, string $title, float $price, string $category): ProductDTO
    {
        return new ProductDTO(
            id: $id,
            title: $title,
            description: "Description for {$title}",
            price: $price,
            category: $category,
            image: "https://example.com/image{$id}.jpg",
            rating: ['rate' => 4.5, 'count' => 100]
        );
    }
}