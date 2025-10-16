<?php

namespace App\Services\Auth;

use App\Contracts\AuthServiceInterface;
use App\DTOs\RegisterDTO;
use App\DTOs\LoginDTO;
use App\DTOs\AuthResponseDTO;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AuthService implements AuthServiceInterface
{
    public function register(RegisterDTO $dto): AuthResponseDTO
    {
        if(Customer::where('email', $dto->email)->exists()) {
            throw new \ValidationException('Email already registered', 422);
        }
        
        return DB::transaction(function () use ($dto) {
            $customer = Customer::create([
                'name' => $dto->name,
                'email' => $dto->email,
                'password' => Hash::make($dto->password),
            ]);

            $token = $customer->createToken('auth_token')->plainTextToken;

            return new AuthResponseDTO(
                message: 'Customer created successfully',
                customer: $customer,
            );
        });
    }

    public function login(LoginDTO $dto): AuthResponseDTO
    {
        if (!Auth::attempt($dto->toArray())) {
            throw ValidationException::withMessages([
                'email' => ['Credenciais inválidas.'],
            ]);
        }

        $customer = Customer::where('email', $dto->email)->firstOrFail();
        $token = $customer->createToken('auth_token')->plainTextToken;

        return new AuthResponseDTO(
            message: 'Login successful',
            customer: $customer,
            access_token: $token,
            token_type: 'Bearer'
        );
    }

    public function logout(): AuthResponseDTO
    {
        $customer = Auth::user();
        
        if ($customer) {
            $customer->currentAccessToken()?->delete();
        }

        return new AuthResponseDTO(
            message: 'Logout successful'
        );
    }

    public function getCurrentCustomer(): object
    {
        return Auth::user();
    }
}