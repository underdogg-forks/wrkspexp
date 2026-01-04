<?php

namespace App\Services;

use App\Models\Timesheet;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * TimesheetService - Handles all business logic for timesheet operations
 * 
 * Follows SOLID principles:
 * - Single Responsibility: Only handles timesheet business logic
 * - Open/Closed: Can be extended without modification
 * - Dependency Inversion: Depends on abstractions (interfaces/models)
 */
class TimesheetService
{
    /**
     * Create a new timesheet entry
     * 
     * @param Task $task
     * @param User $user
     * @param array $data
     * @return Timesheet
     */
    public function create(Task $task, User $user, array $data): Timesheet
    {
        return DB::transaction(function () use ($task, $user, $data) {
            // Early return validation
            if (!$task) {
                throw new \InvalidArgumentException('Task is required');
            }
            
            if (!$user) {
                throw new \InvalidArgumentException('User is required');
            }

            // Generate timesheet number if not provided
            if (!isset($data['timesheet_number'])) {
                $data['timesheet_number'] = $this->generateTimesheetNumber();
            }

            // Set task and user
            $data['task_id'] = $task->id;
            $data['user_id'] = $user->id;

            // Calculate hours if ended_at is provided
            if (isset($data['started_at']) && isset($data['ended_at'])) {
                $data['hours'] = $this->calculateHours($data['started_at'], $data['ended_at']);
            }

            // Set default billable flag if not provided
            if (!isset($data['is_billable'])) {
                $data['is_billable'] = true;
            }

            return Timesheet::create($data);
        });
    }

    /**
     * Update an existing timesheet
     * 
     * @param Timesheet $timesheet
     * @param array $data
     * @return Timesheet
     */
    public function update(Timesheet $timesheet, array $data): Timesheet
    {
        return DB::transaction(function () use ($timesheet, $data) {
            // Recalculate hours if time changed
            if (isset($data['started_at']) || isset($data['ended_at'])) {
                $startedAt = $data['started_at'] ?? $timesheet->started_at;
                $endedAt = $data['ended_at'] ?? $timesheet->ended_at;
                
                if ($endedAt) {
                    $data['hours'] = $this->calculateHours($startedAt, $endedAt);
                }
            }

            $timesheet->update($data);
            return $timesheet->fresh();
        });
    }

    /**
     * Delete a timesheet
     * 
     * @param Timesheet $timesheet
     * @return bool
     */
    public function delete(Timesheet $timesheet): bool
    {
        return $timesheet->delete();
    }

    /**
     * Start a new timesheet (clock in)
     * 
     * @param Task $task
     * @param User $user
     * @param array $data
     * @return Timesheet
     */
    public function startTimer(Task $task, User $user, array $data = []): Timesheet
    {
        $data['started_at'] = now();
        $data['ended_at'] = null;
        $data['hours'] = null;
        
        return $this->create($task, $user, $data);
    }

    /**
     * Stop a timesheet (clock out)
     * 
     * @param Timesheet $timesheet
     * @return Timesheet
     */
    public function stopTimer(Timesheet $timesheet): Timesheet
    {
        // Early return if already stopped
        if ($timesheet->ended_at) {
            return $timesheet;
        }

        return $this->update($timesheet, [
            'ended_at' => now(),
        ]);
    }

    /**
     * Duplicate an existing timesheet
     * 
     * @param Timesheet $timesheet
     * @return Timesheet
     */
    public function duplicate(Timesheet $timesheet): Timesheet
    {
        $data = [
            'task_id' => $timesheet->task_id,
            'user_id' => $timesheet->user_id,
            'started_at' => $timesheet->started_at,
            'ended_at' => $timesheet->ended_at,
            'hours' => $timesheet->hours,
            'is_billable' => $timesheet->is_billable,
        ];

        return Timesheet::create(array_merge($data, [
            'timesheet_number' => $this->generateTimesheetNumber(),
        ]));
    }

    /**
     * Calculate total hours for a task
     * 
     * @param Task $task
     * @return float
     */
    public function calculateTotalHours(Task $task): float
    {
        return (float) $task->timesheets()->sum('hours');
    }

    /**
     * Calculate total billable hours for a task
     * 
     * @param Task $task
     * @return float
     */
    public function calculateBillableHours(Task $task): float
    {
        return (float) $task->timesheets()
            ->where('is_billable', true)
            ->sum('hours');
    }

    /**
     * Calculate total non-billable hours for a task
     * 
     * @param Task $task
     * @return float
     */
    public function calculateNonBillableHours(Task $task): float
    {
        return (float) $task->timesheets()
            ->where('is_billable', false)
            ->sum('hours');
    }

    /**
     * Get active timesheets (currently running) for a user
     * 
     * @param User $user
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActiveTimesheets(User $user)
    {
        return Timesheet::where('user_id', $user->id)
            ->whereNull('ended_at')
            ->with('task')
            ->get();
    }

    /**
     * Generate a unique timesheet number
     * 
     * @return string
     */
    private function generateTimesheetNumber(): string
    {
        $year = date('Y');
        $prefix = "TS-{$year}-";
        
        $lastTimesheet = Timesheet::where('timesheet_number', 'like', "{$prefix}%")
            ->orderBy('timesheet_number', 'desc')
            ->first();

        if ($lastTimesheet) {
            $lastNumber = (int) str_replace($prefix, '', $lastTimesheet->timesheet_number);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Calculate hours between two timestamps
     * 
     * @param mixed $startedAt
     * @param mixed $endedAt
     * @return float
     */
    private function calculateHours($startedAt, $endedAt): float
    {
        $start = Carbon::parse($startedAt);
        $end = Carbon::parse($endedAt);
        
        return round($start->diffInMinutes($end) / 60, 2);
    }
}
