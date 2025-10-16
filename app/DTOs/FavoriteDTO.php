<?php

namespace App\DTOs;

class FavoriteDTO
{
    public function __construct(
        public int $customer_id,
        public int $product_id
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            customer_id: $data['customer_id'],
            product_id: $data['product_id']
        );
    }

    public function toArray(): array
    {
        return [
            'customer_id' => $this->customer_id,
            'product_id' => $this->product_id,
        ];
    }
}