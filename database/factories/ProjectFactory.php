<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        $startedAt = fake()->dateTimeBetween('-1 year', 'now');
        
        return [
            'client_id' => Client::factory(),
            'name' => fake()->catchPhrase(),
            'description' => fake()->optional()->paragraph(),
            'status' => fake()->randomElement(['active', 'on_hold', 'completed', 'cancelled']),
            'started_at' => $startedAt,
            'ended_at' => fake()->optional()->dateTimeBetween($startedAt, '+1 year'),
        ];
    }
}
