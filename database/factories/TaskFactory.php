<?php

namespace Database\Factories;

use App\Enums\TaskStatus;
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
            'task_number' => 'TSK-' . fake()->unique()->numberBetween(10000, 99999),
            'status' => fake()->randomElement(TaskStatus::cases())->value,
            'title' => fake()->sentence(),
            'estimated_hours' => fake()->optional()->numberBetween(1, 100),
            'due_at' => fake()->optional()->dateTimeBetween('now', '+90 days'),
        ];
    }
}
