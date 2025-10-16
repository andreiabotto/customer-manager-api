<?php

namespace App\DTOs;

class CustomerResponseDTO
{
    public function __construct(
        public string $message,
        public ?object $customer = null,
        public ?array $customers = null,
        public ?array $favorites = null,
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'message' => $this->message,
            'customer' => $this->customer,
            'customers' => $this->customers,
            'favorites' => $this->favorites,
        ]);
    }
}