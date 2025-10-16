<?php

namespace App\Contracts;

use App\DTOs\ProductDTO;
use Illuminate\Support\Collection;

interface ProductServiceInterface
{
    public function getAllProducts(): Collection;
    public function getProductById(int $id): ?ProductDTO;
    public function getProductsByCategory(string $category): Collection;
    public function searchProducts(string $query): Collection;
    public function getProductsByIds(array $ids): Collection;
}