<?php

namespace App\DTOs;

class FavoriteResponseDTO
{
    public function __construct(
        public string $message,
        public ?object $favorite = null,
        public ?array $favorites = null,
        public ?array $product_details = null
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'message' => $this->message,
            'favorite' => $this->favorite,
            'favorites' => $this->favorites,
            'product_details' => $this->product_details,
        ]);
    }
}