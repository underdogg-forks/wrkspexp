<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\Product;
use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

class ItemFactory extends Factory
{
    protected $model = Item::class;

    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 10);
        $unitPrice = fake()->randomFloat(2, 10, 500);
        
        return [
            'product_id' => fake()->optional()->randomElement([null, Product::factory()]),
            'task_id' => fake()->optional()->randomElement([null, Task::factory()]),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total' => $quantity * $unitPrice,
        ];
    }
}
