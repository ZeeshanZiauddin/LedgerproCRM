<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'code' => $this->generateCode(),
            'name' => $this->faker->company,
            'email' => $this->faker->unique()->safeEmail,
            'phone' => $this->faker->phoneNumber,
            'address' => $this->faker->address,
            'fax' => $this->faker->phoneNumber,
            'comment' => $this->faker->text(),
            'credit_limit' => $this->faker->randomFloat(2, 100, 10000),
            'status' => $this->faker->randomElement(['active', 'inactive']),
        ];
    }

    /**
     * Generate the next available code.
     *
     * @return string
     */
    private function generateCode(): string
    {
        $lastCustomer = Customer::latest('id')->first();
        $lastCode = $lastCustomer?->code ? intval(substr($lastCustomer->code, 1)) : 0;

        return 'S' . str_pad($lastCode + 1, 3, '0', STR_PAD_LEFT);
    }
}
