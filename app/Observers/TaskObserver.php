<?php

namespace App\Observers;

use App\Models\Task;
use App\Services\TaskService;

/**
 * TaskObserver - Observes Task model events
 * 
 * Follows SOLID principles:
 * - Single Responsibility: Only handles task observation logic
 * - Open/Closed: Can be extended without modification
 */
class TaskObserver
{
    protected TaskService $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    /**
     * Handle the Task "creating" event
     */
    public function creating(Task $task): void
    {
        // Task number will be generated in service layer
    }

    /**
     * Handle the Task "created" event
     */
    public function created(Task $task): void
    {
        // Log task creation or trigger notifications
    }

    /**
     * Handle the Task "updating" event
     */
    public function updating(Task $task): void
    {
        // Validate status transitions
        $this->validateStatusTransition($task);
    }

    /**
     * Handle the Task "updated" event
     */
    public function updated(Task $task): void
    {
        // Trigger events based on status changes
        if ($task->wasChanged('status')) {
            $this->handleStatusChange($task);
        }
    }

    /**
     * Handle the Task "deleting" event
     */
    public function deleting(Task $task): void
    {
        // Cascade delete timesheets if needed
    }

    /**
     * Handle the Task "deleted" event
     */
    public function deleted(Task $task): void
    {
        // Log deletion or notify relevant parties
    }

    /**
     * Validate status transition
     * 
     * @param Task $task
     * @return void
     */
    protected function validateStatusTransition(Task $task): void
    {
        // Early return if status didn't change
        if (!$task->isDirty('status')) {
            return;
        }

        $oldStatus = $task->getOriginal('status');
        $newStatus = $task->status;

        // Add validation logic for valid status transitions
        // For example: Can't complete a cancelled task
    }

    /**
     * Handle status change
     * 
     * @param Task $task
     * @return void
     */
    protected function handleStatusChange(Task $task): void
    {
        // Early return if no status change
        if (!$task->wasChanged('status')) {
            return;
        }

        // Trigger notifications, events, etc. based on new status
        match ($task->status) {
            \App\Enums\TaskStatus::InProgress => $this->handleInProgress($task),
            \App\Enums\TaskStatus::Completed => $this->handleCompleted($task),
            \App\Enums\TaskStatus::Cancelled => $this->handleCancelled($task),
            default => null,
        };
    }

    /**
     * Handle task in progress
     */
    protected function handleInProgress(Task $task): void
    {
        // Send notification to assignee
    }

    /**
     * Handle task completed
     */
    protected function handleCompleted(Task $task): void
    {
        // Send completion notification
    }

    /**
     * Handle task cancelled
     */
    protected function handleCancelled(Task $task): void
    {
        // Send cancellation notification
    }
}
