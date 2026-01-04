<?php

namespace Database\Factories;

use App\Enums\ProjectStatus;
use App\Models\Client;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        $startedAt = fake()->dateTimeBetween('-3 year', '+1 year');
        $client = Client::factory()->create();
        
        return [
            'company_id' => $client->company_id,
            'client_id' => $client->id,
            'project_number' => 'PRJ-' . fake()->unique()->numberBetween(10000, 99999),
            'status' => fake()->randomElement(ProjectStatus::cases())->value,
            'name' => fake()->catchPhrase(),
            'started_at' => $startedAt,
            'ended_at' => fake()->optional()->dateTimeBetween($startedAt, '+3 year'),
        ];
    }
}
