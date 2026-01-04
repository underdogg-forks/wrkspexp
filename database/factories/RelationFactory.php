<?php

namespace Database\Factories;

use App\Models\Relation;
use Illuminate\Database\Eloquent\Factories\Factory;

class RelationFactory extends Factory
{
    protected $model = Relation::class;

    public function definition(): array
    {
        $companyName = fake()->company();

        return [
            'name' => $companyName,
            'slug' => Str::slug($companyName),
        ];
    }
}
