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

        // Generate issued_at first, then expires_at relative to it
        $issuedAt = fake()->dateTimeBetween('-6 months', 'now');
        $expiresAt = fake()->dateTimeBetween($issuedAt, '+60 days from ' . $issuedAt->format('Y-m-d'));

        return [
            'client_id' => Client::factory(),
            'quote_number' => 'QUO-' . fake()->unique()->numberBetween(10000, 99999),
            'issued_at' => $issuedAt,
            'expires_at' => $expiresAt,
            'status' => fake()->randomElement(QuoteStatus::cases())->value,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
        ];
    }
}
