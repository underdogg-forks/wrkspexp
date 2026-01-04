<?php

namespace Database\Factories;

use App\Enums\QuoteStatus;
use App\Models\Client;
use App\Models\Quote;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuoteFactory extends Factory
{
    protected $model = Quote::class;

    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 100, 10000);
        $tax = $subtotal * 0.21;
        $total = $subtotal + $tax;

        return [
            'client_id' => Client::factory(),
            'quote_number' => 'QUO-' . fake()->unique()->numberBetween(10000, 99999),
            'issued_at' => fake()->dateTimeBetween('-6 months', 'now'),
            'expires_at' => fake()->dateTimeBetween('now', '+60 days'),
            'status' => fake()->randomElement(QuoteStatus::cases())->value,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
        ];
    }
}
