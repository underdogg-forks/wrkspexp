<?php

namespace Database\Factories;

use App\Enums\InvoiceStatus;
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

        $client = Client::factory()->create();

        return [
            'client_id' => $client->id,
            'company_id' => $client->company_id,
            'invoice_number' => 'INV-' . fake()->unique()->numberBetween(10000, 99999),
            'issued_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'due_at' => fake()->dateTimeBetween('now', '+30 days'),
            'status' => fake()->randomElement(InvoiceStatus::cases())->value,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => InvoiceStatus::Draft->value,
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => InvoiceStatus::Paid->value,
        ]);
    }
}
