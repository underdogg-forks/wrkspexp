<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Expense;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseFactory extends Factory
{
    protected $model = Expense::class;

    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'title' => fake()->sentence(),
            'description' => fake()->optional()->paragraph(),
            'amount' => fake()->randomFloat(2, 10, 5000),
            'incurred_at' => fake()->dateTimeBetween('-6 months', 'now'),
            'category' => fake()->randomElement(['Travel', 'Office Supplies', 'Software', 'Hardware', 'Consulting', 'Marketing', 'Other']),
            'notes' => fake()->optional()->paragraph(),
        ];
    }
}
