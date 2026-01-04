<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 100, 10000);
        $tax = $subtotal * 0.21;
        $total = $subtotal + $tax;

        return [
            'client_id' => Client::factory(),
            'invoice_number' => 'INV-' . fake()->unique()->numberBetween(10000, 99999),
            'issued_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'due_at' => fake()->dateTimeBetween('now', '+30 days'),
            'status' => fake()->randomElement(['draft', 'sent', 'paid', 'overdue', 'cancelled']),
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
            'notes' => fake()->optional()->paragraph(),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
        ]);
    }
}
