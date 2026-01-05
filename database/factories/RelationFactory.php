<?php

namespace Database\Factories;

use App\Models\Relation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class RelationFactory extends Factory
{
    protected $model = Relation::class;

    public function definition(): array
    {
        $companyName = fake()->company();

        return [
            'relation_number' => 'REL-' . fake()->unique()->numberBetween(10000, 99999),
            'name' => $companyName,
            'slug' => Str::slug($companyName),
            'tax_id' => fake()->optional()->numerify('TAX-########'),
            'tax_number' => fake()->optional()->numerify('##########'),
        ];
    }
}
