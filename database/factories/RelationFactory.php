<?php

namespace Database\Factories;

use App\Models\Relation;
use Illuminate\Database\Eloquent\Factories\Factory;

class RelationFactory extends Factory
{
    protected $model = Relation::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'notes' => fake()->optional()->paragraph(),
        ];
    }
}
