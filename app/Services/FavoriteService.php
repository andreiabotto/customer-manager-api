<?php

namespace App\Services;

use App\Contracts\FavoriteServiceInterface;
use App\Contracts\ProductServiceInterface;
use App\DTOs\FavoriteDTO;
use App\DTOs\FavoriteResponseDTO;
use App\Models\Favorite;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class FavoriteService implements FavoriteServiceInterface
{
    public function __construct(
        private ProductServiceInterface $productService
    ) {}

    public function addToFavorites(FavoriteDTO $dto): FavoriteResponseDTO
    {
        return DB::transaction(function () use ($dto) {
            $product = $this->productService->getProductById($dto->product_id);
            if (!$product) {
                throw new ModelNotFoundException("Product not found in external API.");
            }

            // Verificar se já está nos favoritos
            if ($this->isProductInFavorites($dto->customer_id, $dto->product_id)) {
                throw new \Exception("This product is already in your favorites.");
            }

            $favorite = Favorite::create($dto->toArray());

            return new FavoriteResponseDTO(
                message: "Product '{$product->title}' added to favorites.",
                favorite: $favorite,
                product_details: $product->toArray()
            );
        });
    }

    public function removeFromFavorites(int $favoriteId): FavoriteResponseDTO
    {
        return DB::transaction(function () use ($favoriteId) {
            $favorite = Favorite::find($favoriteId);
            
            if (!$favorite) {
                throw new ModelNotFoundException("Favorite not found.");
            }

            // Buscar detalhes do produto antes de excluir
            $product = $this->productService->getProductById($favorite->product_id);
            $productTitle = $product ? $product->title : 'Product';
            
            $favorite->delete();

            return new FavoriteResponseDTO(
                message: "Product '{$productTitle}' removed from favorites."
            );
        });
    }

    public function removeFromFavoritesByProductId(int $customerId, int $productExternalId): FavoriteResponseDTO
    {
        return DB::transaction(function () use ($customerId, $productExternalId) {
            $favorite = Favorite::where('customer_id', $customerId)
                ->where('product_id', $productExternalId)
                ->first();

            if (!$favorite) {
                throw new ModelNotFoundException("Favorite not found.");
            }

            $product = $this->productService->getProductById($productExternalId);
            $productTitle = $product ? $product->title : 'Product';
            
            $favorite->delete();

            return new FavoriteResponseDTO(
                message: "Product '{$productTitle}' removed from favorites."
            );
        });
    }

    public function getCustomerFavorites(int $customerId): Collection
    {
        $favorites = Favorite::where('customer_id', $customerId)
            ->orderBy('created_at', 'desc')
            ->get();

        $productIds = $favorites->pluck('product_id')->toArray();
        $products = $this->productService->getProductsByIds($productIds);

        return $favorites->map(function ($favorite) use ($products) {
            $product = $products->firstWhere('id', $favorite->product_id);
            
            return [
                'favorite_id' => $favorite->id,
                'added_at' => $favorite->created_at,
                'product' => $product ? $product->toArray() : null
            ];
        });
    }

    public function isProductInFavorites(int $customerId, int $productExternalId): bool
    {
        return Favorite::where('customer_id', $customerId)
            ->where('product_id', $productExternalId)
            ->exists();
    }

    public function getFavoriteById(int $favoriteId): ?object
    {
        return Favorite::find($favoriteId);
    }
}