<?php

namespace Database\Factories;

use App\Models\ExpenseCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseCategoryFactory extends Factory
{
    protected $model = ExpenseCategory::class;

    public function definition(): array
    {
        $name = fake()->randomElement([
            'Travel',
            'Office Supplies',
            'Software',
            'Hardware',
            'Consulting',
            'Marketing',
            'Utilities',
            'Rent',
            'Insurance',
            'Other'
        ]);
        
        return [
            'name' => $name,
            'code' => strtoupper(substr($name, 0, 3)) . fake()->numberBetween(100, 999),
            'is_active' => true,
        ];
    }
}
