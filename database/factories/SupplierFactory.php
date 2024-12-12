<?php

namespace Database\Factories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupplierFactory extends Factory
{
    protected $model = Supplier::class;

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
            'credit_limit' => $this->faker->randomFloat(2, 100, 10000),
            'comment' => $this->faker->text(),
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
        $lastSupplier = Supplier::latest('id')->first();
        $lastCode = $lastSupplier?->code ? intval(substr($lastSupplier->code, 1)) : 0;

        return 'T' . str_pad($lastCode + 1, 3, '0', STR_PAD_LEFT);
    }
}
