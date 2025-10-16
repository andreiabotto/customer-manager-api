<?php

namespace App\DTOs;

class AuthResponseDTO
{
    public function __construct(
        public string $message,
        public ?object $customer = null,
        public ?string $access_token = null,
        public ?string $token_type = null
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'message' => $this->message,
            'customer' => $this->customer,
            'access_token' => $this->access_token,
            'token_type' => $this->token_type,
        ]);
    }
}