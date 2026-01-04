<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition(): array
    {
        $name = fake()->company();
        
        return [
            'company_number' => 'COM-' . fake()->unique()->numberBetween(10000, 99999),
            'name' => $name,
            'slug' => Str::slug($name) . '-' . fake()->unique()->numberBetween(1, 9999),
            'description' => fake()->optional()->sentence(),
        ];
    }
}
