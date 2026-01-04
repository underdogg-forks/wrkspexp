<?php

namespace App\Observers;

use App\Models\Project;
use App\Services\ProjectService;

/**
 * ProjectObserver - Observes Project model events
 * 
 * Follows SOLID principles:
 * - Single Responsibility: Only handles project observation logic
 * - Open/Closed: Can be extended without modification
 */
class ProjectObserver
{
    protected ProjectService $projectService;

    public function __construct(ProjectService $projectService)
    {
        $this->projectService = $projectService;
    }

    /**
     * Handle the Project "creating" event
     */
    public function creating(Project $project): void
    {
        // Generate project number if not set
        if (!$project->project_number && $project->company) {
            // Project number will be generated in service layer
        }
    }

    /**
     * Handle the Project "created" event
     */
    public function created(Project $project): void
    {
        // Log project creation or trigger notifications
    }

    /**
     * Handle the Project "updating" event
     */
    public function updating(Project $project): void
    {
        // Validate status transitions
        $this->validateStatusTransition($project);
    }

    /**
     * Handle the Project "updated" event
     */
    public function updated(Project $project): void
    {
        // Trigger events based on status changes
        if ($project->wasChanged('status')) {
            $this->handleStatusChange($project);
        }
    }

    /**
     * Handle the Project "deleting" event
     */
    public function deleting(Project $project): void
    {
        // Cascade delete tasks if needed
    }

    /**
     * Handle the Project "deleted" event
     */
    public function deleted(Project $project): void
    {
        // Log deletion or notify relevant parties
    }

    /**
     * Validate status transition
     * 
     * @param Project $project
     * @return void
     */
    protected function validateStatusTransition(Project $project): void
    {
        // Early return if status didn't change
        if (!$project->isDirty('status')) {
            return;
        }

        $oldStatus = $project->getOriginal('status');
        $newStatus = $project->status;

        // Add validation logic for valid status transitions
        // For example: Can't reactivate a cancelled project
    }

    /**
     * Handle status change
     * 
     * @param Project $project
     * @return void
     */
    protected function handleStatusChange(Project $project): void
    {
        // Early return if no status change
        if (!$project->wasChanged('status')) {
            return;
        }

        // Trigger notifications, events, etc. based on new status
        match ($project->status) {
            \App\Enums\ProjectStatus::Completed => $this->handleCompleted($project),
            \App\Enums\ProjectStatus::OnHold => $this->handleOnHold($project),
            \App\Enums\ProjectStatus::Cancelled => $this->handleCancelled($project),
            default => null,
        };
    }

    /**
     * Handle project completed
     */
    protected function handleCompleted(Project $project): void
    {
        // Send completion notification to team
    }

    /**
     * Handle project on hold
     */
    protected function handleOnHold(Project $project): void
    {
        // Send on hold notification
    }

    /**
     * Handle project cancelled
     */
    protected function handleCancelled(Project $project): void
    {
        // Send cancellation notification
    }
}
