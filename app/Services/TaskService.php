<?php

namespace App\Services;

use App\Models\Task;
use App\Models\Company;
use App\Enums\TaskStatus;
use Illuminate\Support\Facades\DB;

/**
 * TaskService - Handles all business logic for task operations
 * 
 * Follows SOLID principles:
 * - Single Responsibility: Only handles task business logic
 * - Open/Closed: Can be extended without modification
 * - Dependency Inversion: Depends on abstractions (interfaces/models)
 */
class TaskService
{
    /**
     * Create a new task
     * 
     * @param Company $company
     * @param array $data
     * @return Task
     */
    public function create(Company $company, array $data): Task
    {
        return DB::transaction(function () use ($company, $data) {
            // Early return if no company
            if (!$company) {
                throw new \InvalidArgumentException('Company is required');
            }

            // Validate tenant scoping: ensure client belongs to the company
            if (isset($data['client_id'])) {
                $client = \App\Models\Client::find($data['client_id']);
                if (!$client || $client->company_id !== $company->id) {
                    throw new \InvalidArgumentException('Client does not belong to the specified company');
                }
            }

            // Generate task number if not provided
            if (!isset($data['task_number'])) {
                $data['task_number'] = $this->generateTaskNumber($company);
            }

            // Set default status if not provided
            if (!isset($data['status'])) {
                $data['status'] = TaskStatus::Pending;
            }

            // Create task
            $task = Task::create($data);

            return $task->fresh();
        });
    }

    /**
     * Update an existing task
     * 
     * @param Task $task
     * @param array $data
     * @return Task
     */
    public function update(Task $task, array $data): Task
    {
        return DB::transaction(function () use ($task, $data) {
            $task->update($data);
            return $task->fresh();
        });
    }

    /**
     * Mark task as in progress
     * 
     * @param Task $task
     * @return Task
     */
    public function markAsInProgress(Task $task): Task
    {
        // Early return if already in progress
        if ($task->status === TaskStatus::InProgress) {
            return $task;
        }

        $task->update([
            'status' => TaskStatus::InProgress,
        ]);

        return $task->fresh();
    }

    /**
     * Mark task as completed
     * 
     * @param Task $task
     * @return Task
     */
    public function markAsCompleted(Task $task): Task
    {
        // Early return if already completed
        if ($task->status === TaskStatus::Completed) {
            return $task;
        }

        $task->update([
            'status' => TaskStatus::Completed,
        ]);

        return $task->fresh();
    }

    /**
     * Cancel a task
     * 
     * @param Task $task
     * @return Task
     */
    public function cancel(Task $task): Task
    {
        // Early return if already cancelled
        if ($task->status === TaskStatus::Cancelled) {
            return $task;
        }

        $task->update([
            'status' => TaskStatus::Cancelled,
        ]);

        return $task->fresh();
    }

    /**
     * Duplicate a task
     * 
     * @param Task $task
     * @return Task
     */
    public function duplicate(Task $task): Task
    {
        return DB::transaction(function () use ($task) {
            $newTask = $task->replicate();
            $newTask->task_number = $this->generateTaskNumber($task->client->company);
            $newTask->status = TaskStatus::Pending;
            $newTask->save();

            return $newTask->fresh();
        });
    }

    /**
     * Calculate total hours logged for a task
     * 
     * @param Task $task
     * @return float
     */
    public function calculateTotalHours(Task $task): float
    {
        return $task->timesheets()->sum('hours') ?? 0.0;
    }

    /**
     * Calculate hours remaining (estimated - logged)
     * 
     * @param Task $task
     * @return float|null
     */
    public function calculateRemainingHours(Task $task): ?float
    {
        // Early return if no estimated hours
        if (!$task->estimated_hours) {
            return null;
        }

        $totalHours = $this->calculateTotalHours($task);
        return max(0, $task->estimated_hours - $totalHours);
    }

    /**
     * Generate unique task number
     * 
     * @param Company $company
     * @return string
     */
    protected function generateTaskNumber(Company $company): string
    {
        $prefix = 'TSK';
        $year = date('Y');
        $lastTask = Task::where('task_number', 'LIKE', "{$prefix}-{$year}-%")
            ->orderBy('task_number', 'desc')
            ->first();

        if (!$lastTask) {
            $sequence = 1;
        } else {
            $lastNumber = intval(substr($lastTask->task_number, -6));
            $sequence = $lastNumber + 1;
        }

        return sprintf('%s-%s-%06d', $prefix, $year, $sequence);
    }
}
