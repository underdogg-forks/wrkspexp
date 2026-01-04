<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'project_id' => fake()->optional()->randomElement([null, Project::factory()]),
            'title' => fake()->sentence(),
            'description' => fake()->optional()->paragraph(),
            'status' => fake()->randomElement(['pending', 'in_progress', 'completed', 'cancelled']),
            'estimated_hours' => fake()->optional()->numberBetween(1, 100),
            'due_at' => fake()->optional()->dateTimeBetween('now', '+90 days'),
        ];
    }
}
