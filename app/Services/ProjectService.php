<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Company;
use App\Enums\ProjectStatus;
use Illuminate\Support\Facades\DB;

/**
 * ProjectService - Handles all business logic for project operations
 * 
 * Follows SOLID principles:
 * - Single Responsibility: Only handles project business logic
 * - Open/Closed: Can be extended without modification
 * - Dependency Inversion: Depends on abstractions (interfaces/models)
 */
class ProjectService
{
    /**
     * Create a new project
     * 
     * @param Company $company
     * @param array $data
     * @return Project
     */
    public function create(Company $company, array $data): Project
    {
        return DB::transaction(function () use ($company, $data) {
            // Early return if no company
            if (!$company) {
                throw new \InvalidArgumentException('Company is required');
            }

            // Generate project number if not provided
            if (!isset($data['project_number'])) {
                $data['project_number'] = $this->generateProjectNumber($company);
            }

            // Set default status if not provided
            if (!isset($data['status'])) {
                $data['status'] = ProjectStatus::Active;
            }

            // Create project
            $project = $company->projects()->create($data);

            return $project->fresh();
        });
    }

    /**
     * Update an existing project
     * 
     * @param Project $project
     * @param array $data
     * @return Project
     */
    public function update(Project $project, array $data): Project
    {
        return DB::transaction(function () use ($project, $data) {
            $project->update($data);
            return $project->fresh();
        });
    }

    /**
     * Mark project as completed
     * 
     * @param Project $project
     * @return Project
     */
    public function markAsCompleted(Project $project): Project
    {
        // Early return if already completed
        if ($project->status === ProjectStatus::Completed) {
            return $project;
        }

        $project->update([
            'status' => ProjectStatus::Completed,
            'ended_at' => now(),
        ]);

        return $project->fresh();
    }

    /**
     * Put project on hold
     * 
     * @param Project $project
     * @return Project
     */
    public function putOnHold(Project $project): Project
    {
        // Early return if already on hold
        if ($project->status === ProjectStatus::OnHold) {
            return $project;
        }

        $project->update([
            'status' => ProjectStatus::OnHold,
        ]);

        return $project->fresh();
    }

    /**
     * Cancel a project
     * 
     * @param Project $project
     * @return Project
     */
    public function cancel(Project $project): Project
    {
        // Early return if already cancelled
        if ($project->status === ProjectStatus::Cancelled) {
            return $project;
        }

        $project->update([
            'status' => ProjectStatus::Cancelled,
            'ended_at' => now(),
        ]);

        return $project->fresh();
    }

    /**
     * Duplicate a project
     * 
     * @param Project $project
     * @return Project
     */
    public function duplicate(Project $project): Project
    {
        return DB::transaction(function () use ($project) {
            $newProject = $project->replicate();
            $newProject->project_number = $this->generateProjectNumber($project->company);
            $newProject->status = ProjectStatus::Active;
            $newProject->started_at = null;
            $newProject->ended_at = null;
            $newProject->save();

            // Duplicate tasks
            foreach ($project->tasks as $task) {
                $newTask = $task->replicate();
                $newTask->project_id = $newProject->id;
                $newTask->save();
            }

            return $newProject->fresh();
        });
    }

    /**
     * Calculate project completion percentage
     * 
     * @param Project $project
     * @return float
     */
    public function calculateCompletionPercentage(Project $project): float
    {
        $totalTasks = $project->tasks()->count();

        // Early return if no tasks
        if ($totalTasks === 0) {
            return 0.0;
        }

        $completedTasks = $project->tasks()
            ->where('status', \App\Enums\TaskStatus::Completed->value)
            ->count();

        return round(($completedTasks / $totalTasks) * 100, 2);
    }

    /**
     * Generate unique project number
     * 
     * @param Company $company
     * @return string
     */
    protected function generateProjectNumber(Company $company): string
    {
        $prefix = 'PRJ';
        $year = date('Y');
        $lastProject = $company->projects()
            ->where('project_number', 'LIKE', "{$prefix}-{$year}-%")
            ->orderBy('project_number', 'desc')
            ->first();

        if (!$lastProject) {
            $sequence = 1;
        } else {
            $lastNumber = intval(substr($lastProject->project_number, -6));
            $sequence = $lastNumber + 1;
        }

        return sprintf('%s-%s-%06d', $prefix, $year, $sequence);
    }
}
