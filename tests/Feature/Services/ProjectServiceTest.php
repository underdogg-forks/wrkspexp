<?php

namespace Tests\Feature\Services;

use App\Enums\ProjectStatus;
use App\Enums\TaskStatus;
use App\Models\Company;
use App\Models\Project;
use App\Models\Task;
use App\Services\ProjectService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ProjectService $projectService;
    protected Company $company;

    protected function setUp(): void
    {
        parent::setUp();
        $this->projectService = app(ProjectService::class);
        $this->company = Company::factory()->create();
    }

    /**
     * @test
     * Arrange: Company exists and project data is provided
     * Act: Create project via service
     * Assert: Project is created with correct data
     */
    public function it_creates_a_project_with_generated_number(): void
    {
        // Arrange
        $data = [
            'name' => 'Test Project',
            'client_id' => 1,
            'budget' => 10000.00,
        ];

        // Act
        $project = $this->projectService->create($this->company, $data);

        // Assert
        $this->assertInstanceOf(Project::class, $project);
        $this->assertNotEmpty($project->project_number);
        $this->assertStringStartsWith('PRJ-', $project->project_number);
        $this->assertEquals(ProjectStatus::Active, $project->status);
        $this->assertEquals('Test Project', $project->name);
        $this->assertEquals(10000.00, $project->budget);
    }

    /**
     * @test
     * Arrange: Company and project data with custom project number
     * Act: Create project via service
     * Assert: Project uses custom project number
     */
    public function it_creates_a_project_with_custom_project_number(): void
    {
        // Arrange
        $data = [
            'project_number' => 'CUSTOM-PRJ-001',
            'name' => 'Custom Project',
            'client_id' => 1,
            'budget' => 5000.00,
        ];

        // Act
        $project = $this->projectService->create($this->company, $data);

        // Assert
        $this->assertEquals('CUSTOM-PRJ-001', $project->project_number);
    }

    /**
     * @test
     * Arrange: Project exists
     * Act: Update project via service
     * Assert: Project is updated with new data
     */
    public function it_updates_an_existing_project(): void
    {
        // Arrange
        $project = Project::factory()->for($this->company)->create([
            'name' => 'Original Name',
            'budget' => 5000.00,
        ]);
        $data = [
            'name' => 'Updated Name',
            'budget' => 7500.00,
        ];

        // Act
        $updatedProject = $this->projectService->update($project, $data);

        // Assert
        $this->assertEquals('Updated Name', $updatedProject->name);
        $this->assertEquals(7500.00, $updatedProject->budget);
    }

    /**
     * @test
     * Arrange: Active project exists
     * Act: Mark project as completed via service
     * Assert: Project status is Completed and ended_at is set
     */
    public function it_marks_project_as_completed(): void
    {
        // Arrange
        $project = Project::factory()->for($this->company)->create([
            'status' => ProjectStatus::Active,
            'ended_at' => null,
        ]);

        // Act
        $completedProject = $this->projectService->markAsCompleted($project);

        // Assert
        $this->assertEquals(ProjectStatus::Completed, $completedProject->status);
        $this->assertNotNull($completedProject->ended_at);
    }

    /**
     * @test
     * Arrange: Project already completed
     * Act: Mark project as completed again
     * Assert: Returns same project without changes (early return)
     */
    public function it_returns_early_when_project_already_completed(): void
    {
        // Arrange
        $endedAt = now()->subDays(3);
        $project = Project::factory()->for($this->company)->create([
            'status' => ProjectStatus::Completed,
            'ended_at' => $endedAt,
        ]);

        // Act
        $result = $this->projectService->markAsCompleted($project);

        // Assert
        $this->assertEquals(ProjectStatus::Completed, $result->status);
        $this->assertEquals($endedAt->toDateTimeString(), $result->ended_at->toDateTimeString());
    }

    /**
     * @test
     * Arrange: Active project exists
     * Act: Put project on hold via service
     * Assert: Project status is OnHold
     */
    public function it_puts_project_on_hold(): void
    {
        // Arrange
        $project = Project::factory()->for($this->company)->create([
            'status' => ProjectStatus::Active,
        ]);

        // Act
        $onHoldProject = $this->projectService->putOnHold($project);

        // Assert
        $this->assertEquals(ProjectStatus::OnHold, $onHoldProject->status);
    }

    /**
     * @test
     * Arrange: Project already on hold
     * Act: Put project on hold again
     * Assert: Returns same project without changes (early return)
     */
    public function it_returns_early_when_project_already_on_hold(): void
    {
        // Arrange
        $project = Project::factory()->for($this->company)->create([
            'status' => ProjectStatus::OnHold,
        ]);

        // Act
        $result = $this->projectService->putOnHold($project);

        // Assert
        $this->assertEquals(ProjectStatus::OnHold, $result->status);
    }

    /**
     * @test
     * Arrange: Active project exists
     * Act: Cancel project via service
     * Assert: Project status is Cancelled and ended_at is set
     */
    public function it_cancels_a_project(): void
    {
        // Arrange
        $project = Project::factory()->for($this->company)->create([
            'status' => ProjectStatus::Active,
            'ended_at' => null,
        ]);

        // Act
        $cancelledProject = $this->projectService->cancel($project);

        // Assert
        $this->assertEquals(ProjectStatus::Cancelled, $cancelledProject->status);
        $this->assertNotNull($cancelledProject->ended_at);
    }

    /**
     * @test
     * Arrange: Project already cancelled
     * Act: Cancel project again
     * Assert: Returns same project without changes (early return)
     */
    public function it_returns_early_when_project_already_cancelled(): void
    {
        // Arrange
        $endedAt = now()->subDays(1);
        $project = Project::factory()->for($this->company)->create([
            'status' => ProjectStatus::Cancelled,
            'ended_at' => $endedAt,
        ]);

        // Act
        $result = $this->projectService->cancel($project);

        // Assert
        $this->assertEquals(ProjectStatus::Cancelled, $result->status);
        $this->assertEquals($endedAt->toDateTimeString(), $result->ended_at->toDateTimeString());
    }

    /**
     * @test
     * Arrange: Project with tasks exists
     * Act: Duplicate project via service
     * Assert: New project created with new number and active status
     */
    public function it_duplicates_a_project_with_new_number(): void
    {
        // Arrange
        $project = Project::factory()->for($this->company)->create([
            'project_number' => 'PRJ-2026-000001',
            'status' => ProjectStatus::Completed,
            'name' => 'Original Project',
            'budget' => 15000.00,
        ]);

        // Act
        $duplicatedProject = $this->projectService->duplicate($project);

        // Assert
        $this->assertNotEquals($project->id, $duplicatedProject->id);
        $this->assertNotEquals($project->project_number, $duplicatedProject->project_number);
        $this->assertEquals(ProjectStatus::Active, $duplicatedProject->status);
        $this->assertEquals('Original Project', $duplicatedProject->name);
        $this->assertEquals(15000.00, $duplicatedProject->budget);
        $this->assertNull($duplicatedProject->started_at);
        $this->assertNull($duplicatedProject->ended_at);
    }

    /**
     * @test
     * Arrange: Project with tasks exists
     * Act: Duplicate project via service
     * Assert: Tasks are duplicated to new project
     */
    public function it_duplicates_project_tasks_when_duplicating_project(): void
    {
        // Arrange
        $project = Project::factory()->for($this->company)->create();
        Task::factory()->for($project)->for($this->company)->count(3)->create();

        // Act
        $duplicatedProject = $this->projectService->duplicate($project);

        // Assert
        $this->assertCount(3, $project->tasks);
        $this->assertCount(3, $duplicatedProject->tasks);
        $this->assertNotEquals($project->tasks->first()->id, $duplicatedProject->tasks->first()->id);
    }

    /**
     * @test
     * Arrange: Project without tasks
     * Act: Calculate completion percentage
     * Assert: Returns 0% (early return)
     */
    public function it_returns_zero_completion_when_project_has_no_tasks(): void
    {
        // Arrange
        $project = Project::factory()->for($this->company)->create();

        // Act
        $completion = $this->projectService->calculateCompletionPercentage($project);

        // Assert
        $this->assertEquals(0.0, $completion);
    }

    /**
     * @test
     * Arrange: Project with mixed status tasks
     * Act: Calculate completion percentage
     * Assert: Returns correct percentage based on completed tasks
     */
    public function it_calculates_completion_percentage_correctly(): void
    {
        // Arrange
        $project = Project::factory()->for($this->company)->create();
        Task::factory()->for($project)->for($this->company)->create(['status' => TaskStatus::Completed]);
        Task::factory()->for($project)->for($this->company)->create(['status' => TaskStatus::Completed]);
        Task::factory()->for($project)->for($this->company)->create(['status' => TaskStatus::Pending]);
        Task::factory()->for($project)->for($this->company)->create(['status' => TaskStatus::InProgress]);

        // Act
        $completion = $this->projectService->calculateCompletionPercentage($project);

        // Assert
        $this->assertEquals(50.0, $completion); // 2 out of 4 tasks completed
    }

    /**
     * @test
     * Arrange: Multiple projects exist for company
     * Act: Create new project
     * Assert: Project number increments correctly
     */
    public function it_generates_sequential_project_numbers(): void
    {
        // Arrange
        Project::factory()->for($this->company)->create([
            'project_number' => 'PRJ-2026-000001',
        ]);
        Project::factory()->for($this->company)->create([
            'project_number' => 'PRJ-2026-000002',
        ]);

        // Act
        $project = $this->projectService->create($this->company, [
            'name' => 'New Project',
            'client_id' => 1,
            'budget' => 8000.00,
        ]);

        // Assert
        $this->assertEquals('PRJ-2026-000003', $project->project_number);
    }

    /**
     * @test
     * Arrange: No company provided
     * Act: Attempt to create project
     * Assert: Exception thrown (early return validation)
     */
    public function it_throws_exception_when_creating_project_without_company(): void
    {
        // Arrange
        $data = [
            'name' => 'Test Project',
            'client_id' => 1,
        ];

        // Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Company is required');

        // Act
        $this->projectService->create(null, $data);
    }
}
