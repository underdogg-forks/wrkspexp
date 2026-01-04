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
        $startedAt = fake()->dateTimeBetween('-1 year', 'now');
        $client = Client::factory()->create();
        
        return [
            'company_id' => $client->company_id,
            'client_id' => $client->id,
            'project_number' => 'PRJ-' . fake()->unique()->numberBetween(10000, 99999),
            'name' => fake()->catchPhrase(),
            'status' => fake()->randomElement(ProjectStatus::cases())->value,
            'started_at' => $startedAt,
            'ended_at' => fake()->optional()->dateTimeBetween($startedAt, '+1 year'),
        ];
    }
}
