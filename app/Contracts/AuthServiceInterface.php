<?php

namespace App\Contracts;

use App\DTOs\RegisterDTO;
use App\DTOs\LoginDTO;
use App\DTOs\AuthResponseDTO;

interface AuthServiceInterface
{
    public function register(RegisterDTO $dto): AuthResponseDTO;
    public function login(LoginDTO $dto): AuthResponseDTO;
    public function logout(): AuthResponseDTO;
    public function getCurrentCustomer(): object;
}