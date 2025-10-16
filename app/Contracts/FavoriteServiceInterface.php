<?php

namespace App\Contracts;

use App\DTOs\FavoriteDTO;
use App\DTOs\FavoriteResponseDTO;
use Illuminate\Support\Collection;

interface FavoriteServiceInterface
{
    public function addToFavorites(FavoriteDTO $dto): FavoriteResponseDTO;
    public function removeFromFavorites(int $favoriteId): FavoriteResponseDTO;
    public function removeFromFavoritesByProductId(int $customerId, int $productExternalId): FavoriteResponseDTO;
    public function getCustomerFavorites(int $customerId): Collection;
    public function isProductInFavorites(int $customerId, int $productExternalId): bool;
    public function getFavoriteById(int $favoriteId): ?object;
}