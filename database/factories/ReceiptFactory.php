<?php

namespace Database\Factories;

use App\Models\Receipt;
use App\Models\User;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReceiptFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Fetch available users and customers
        $userId = User::inRandomOrder()->first()->id; // Randomly select an existing user
        $customerId = Customer::inRandomOrder()->first()->id; // Randomly select an existing customer

        return [
            'total' => $this->faker->randomFloat(2, 100, 1000), // Random total between 100 and 1000
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'), // Random date within the past year
            'updated_at' => now(),
            'user_id' => $userId, // Assign a random user ID
            'customer_id' => $customerId, // Assign a random customer ID
        ];
    }
}
