<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Favorite;
use Illuminate\Database\Eloquent\Factories\Factory;

class FavoriteFactory extends Factory
{
    protected $model = Favorite::class;

    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'product_id' => $this->faker->numberBetween(1, 20),
        ];
    }
}