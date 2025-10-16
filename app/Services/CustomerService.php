<?php

namespace App\Services;

use App\Contracts\CustomerServiceInterface;
use App\DTOs\CustomerResponseDTO;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Contracts\FavoriteServiceInterface;

class CustomerService implements CustomerServiceInterface
{
    public function __construct(
        private FavoriteServiceInterface $favoriteService
    ) {}

    public function getProfile(): array
    {
        $customer = Auth::user();
        $favorites = $this->favoriteService->getCustomerFavorites($customer->id);

        return [
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'email_verified_at' => $customer->email_verified_at,
                'created_at' => $customer->created_at,
                'updated_at' => $customer->updated_at,
            ],
            'favorites' => $favorites->toArray()
        ];
    }

    public function updateProfile(array $data): object
    {
        $customer = Auth::user();
        $customer->update($data);

        return $customer->fresh();
    }

    public function getAllCustomers(): array
    {
        $customers = Customer::select('id', 'name', 'email', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get();

        return $customers->map(function ($customer) {
            $favoritesCount = $this->favoriteService->getCustomerFavorites($customer->id)->count();
            
            return [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'created_at' => $customer->created_at,
                'favorites_count' => $favoritesCount
            ];
        })->toArray();
    }

    public function deleteCustomer(int $id): CustomerResponseDTO
    {
        return DB::transaction(function () use ($id) {
            $customer = Customer::find($id);
            
            if (!$customer) {
                throw new ModelNotFoundException("Customer not found.");
            }

            $currentCustomer = Auth::user();
            if (!$currentCustomer) {
                throw new \Exception("User not authenticated.");
            }
            
            if ($customer->id === $currentCustomer->id) {
                throw new \Exception("You cannot delete your own account.");
            }

            $favorites = $this->favoriteService->getCustomerFavorites($customer->id);
            $favoritesCount = $favorites->count();

            $customer->favorites()->delete();

            $remainingFavorites = $this->favoriteService->getCustomerFavorites($customer->id);

            $customerName = $customer->name;
            
            $deleted = $customer->delete();

            return new CustomerResponseDTO(
                message: "Customer '{$customerName}' successfully deleted. {$favoritesCount} favorites removed."
            );
        });
    }
}