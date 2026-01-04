<?php

namespace Database\Factories;

use App\Enums\ProductType;
use App\Models\Company;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'name' => fake()->words(3, true),
            'type' => fake()->randomElement(ProductType::cases())->value,
            'price' => fake()->randomFloat(2, 10, 1000),
            'sku' => fake()->optional()->bothify('SKU-####-????'),
        ];
    }

    public function service(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => ProductType::Service->value,
        ]);
    }

    public function physical(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => ProductType::Physical->value,
        ]);
    }
}
