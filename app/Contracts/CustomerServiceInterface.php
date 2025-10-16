<?php

namespace App\Contracts;

use App\DTOs\CustomerResponseDTO;

interface CustomerServiceInterface
{
    public function getProfile(): array;
    public function updateProfile(array $data): object;
    public function getAllCustomers(): array;
    public function deleteCustomer(int $id): CustomerResponseDTO;
}