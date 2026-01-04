<?php

namespace Database\Factories;

use App\Enums\ExpenseStatus;
use App\Models\ExpenseCategory;
use App\Models\Expense;
use App\Models\Relation;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseFactory extends Factory
{
    protected $model = Expense::class;

    public function definition(): array
    {
        return [
            'vendor_id' => Relation::factory(),
            'expense_category_id' => ExpenseCategory::factory(),
            'expense_number' => 'EXP-' . fake()->unique()->numberBetween(10000, 99999),
            'title' => fake()->sentence(),
            'status' => fake()->randomElement(ExpenseStatus::cases())->value,
            'amount' => fake()->randomFloat(2, 10, 5000),
            'incurred_at' => fake()->dateTimeBetween('-6 months', 'now'),
        ];
    }
}
