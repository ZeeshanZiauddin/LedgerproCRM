<?php

namespace Database\Factories;

use App\Models\Card;
use App\Models\CardPassenger;
use App\Models\Customer;
use App\Models\Inquiry;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CardFactory extends Factory
{
    /**
     * The name of the corresponding model.
     *
     * @var string
     */
    protected $model = Card::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'card_name' => $this->faker->sentence(3),
            'user_id' => User::inRandomOrder()->value('id') ?: User::factory()->create()->id,
            'customer' => Customer::inRandomOrder()->value('id') ?: Customer::factory()->create()->id,
            'supplier' => Supplier::inRandomOrder()->value('id') ?: Supplier::factory()->create()->id,
            'inquiry_id' => Inquiry::inRandomOrder()->value('id') ?: Inquiry::factory()->create()->id,
            'contact_name' => $this->faker->name(),
            'contact_email' => $this->faker->unique()->safeEmail(),
            'contact_mobile' => $this->faker->phoneNumber(),
            'contact_home_number' => $this->faker->optional()->phoneNumber(),
            'contact_other_number' => $this->faker->optional()->phoneNumber(),
            'contact_address' => $this->faker->address(),
            'sales_price' => $this->faker->randomFloat(2, 1000, 10000),
            'net_cost' => $this->faker->randomFloat(2, 800, 9000),
            'tax' => $this->faker->randomFloat(2, 50, 500),
            'margin' => $this->faker->randomFloat(2, 100, 500),
        ];
    }

    /**
     * Configure the factory to add passengers.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function withPassengers(int $count = 2)
    {
        return $this->afterCreating(function (Card $card) use ($count) {
            CardPassenger::factory($count)->create(['card_id' => $card->id]);
        });
    }
}

class CardPassengerFactory extends Factory
{
    /**
     * The name of the corresponding model.
     *
     * @var string
     */
    protected $model = CardPassenger::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'card_id' => Card::factory(),
            'name' => $this->faker->name(),
            'ticket_1' => Str::random(10),
            'ticket_2' => $this->faker->optional()->randomNumber(8),
            'issue_date' => Carbon::now()->subDays($this->faker->numberBetween(1, 30)),
            'option_date' => Carbon::now()->addDays($this->faker->numberBetween(1, 30)),
            'pnr' => strtoupper(Str::random(6)),
            'sale' => $this->faker->randomFloat(2, 500, 2000),
            'cost' => $this->faker->randomFloat(2, 400, 1500),
            'tax' => $this->faker->randomFloat(2, 20, 200),
            'margin' => $this->faker->randomFloat(2, 50, 300),
        ];
    }
}
