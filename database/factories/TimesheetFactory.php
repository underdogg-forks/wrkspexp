<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\Timesheet;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Timesheet>
 */
class TimesheetFactory extends Factory
{
    protected $model = Timesheet::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startedAt = fake()->dateTimeBetween('-1 month', 'now');
        $endedAt = fake()->dateTimeBetween($startedAt, 'now');
        $diffInMinutes = ($endedAt->getTimestamp() - $startedAt->getTimestamp()) / 60;
        $hours = round($diffInMinutes / 60, 2);

        return [
            'task_id' => Task::factory(),
            'user_id' => User::factory(),
            'timesheet_number' => 'TS-' . date('Y') . '-' . str_pad(fake()->unique()->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
            'started_at' => $startedAt,
            'ended_at' => $endedAt,
            'hours' => $hours,
            'is_billable' => fake()->boolean(80), // 80% chance of being billable
        ];
    }

    /**
     * Indicate that the timesheet is currently running (no end time).
     */
    public function running(): static
    {
        return $this->state(fn (array $attributes) => [
            'ended_at' => null,
            'hours' => null,
        ]);
    }

    /**
     * Indicate that the timesheet is billable.
     */
    public function billable(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_billable' => true,
        ]);
    }

    /**
     * Indicate that the timesheet is non-billable.
     */
    public function nonBillable(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_billable' => false,
        ]);
    }
}
