<?php

namespace Database\Factories;

use App\Models\Inquiry;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Carbon\Carbon;

class InquiryFactory extends Factory
{
    /**
     * The name of the corresponding model.
     *
     * @var string
     */
    protected $model = Inquiry::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'inquiry_name' => $this->faker->sentence(3),
            'user_id' => User::inRandomOrder()->value('id') ?: User::factory()->create()->id,
            'status' => $this->faker->randomElement(['pending', 'approved', 'declined']),
            'contact_name' => $this->faker->name(),
            'contact_email' => $this->faker->unique()->safeEmail(),
            'contact_mobile' => $this->faker->phoneNumber(),
            'contact_home_number' => $this->faker->optional()->phoneNumber(),
            'contact_address' => $this->faker->address(),
            'price_option' => $this->faker->randomFloat(2, 100, 10000),
            'option_date' => Carbon::now()->addDays($this->faker->numberBetween(1, 30)),
            'card_no' => Str::random(16),
            'pnr' => strtoupper(Str::random(6)),
            'filter_point' => $this->faker->word(),
        ];
    }
}
