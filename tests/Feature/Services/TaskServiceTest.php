<?php

namespace Tests\Feature\Services;

use App\Enums\TaskStatus;
use App\Models\Client;
use App\Models\Company;
use App\Models\Task;
use App\Models\Timesheet;
use App\Services\TaskService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskServiceTest extends TestCase
{
    use RefreshDatabase;

    protected TaskService $taskService;
    protected Company $company;
    protected Client $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->taskService = app(TaskService::class);
        $this->company = Company::factory()->create();
        $this->client = Client::factory()->create(['company_id' => $this->company->id]);
    }

    /**
     * @test
     * Arrange: Company exists and task data is provided
     * Act: Create task via service
     * Assert: Task is created with correct data
     */
    public function it_creates_a_task_with_generated_number(): void
    {
        // Arrange
        $data = [
            'title' => 'Test Task',
            'client_id' => $this->client->id,
            'estimated_hours' => 10,
        ];

        // Act
        $task = $this->taskService->create($this->company, $data);

        // Assert
        $this->assertInstanceOf(Task::class, $task);
        $this->assertNotEmpty($task->task_number);
        $this->assertStringStartsWith('TSK-', $task->task_number);
        $this->assertEquals(TaskStatus::Pending, $task->status);
        $this->assertEquals('Test Task', $task->title);
        $this->assertEquals(10, $task->estimated_hours);
    }

    /**
     * @test
     * Arrange: Company and task data with custom task number
     * Act: Create task via service
     * Assert: Task uses custom task number
     */
    public function it_creates_a_task_with_custom_task_number(): void
    {
        // Arrange
        $data = [
            'task_number' => 'CUSTOM-TSK-001',
            'title' => 'Custom Task',
            'client_id' => $this->client->id,
        ];

        // Act
        $task = $this->taskService->create($this->company, $data);

        // Assert
        $this->assertEquals('CUSTOM-TSK-001', $task->task_number);
    }

    /**
     * @test
     * Arrange: Task data with client from different company
     * Act: Attempt to create task
     * Assert: Exception is thrown for tenant validation
     */
    public function it_validates_tenant_scoping_on_create(): void
    {
        // Arrange
        $otherCompany = Company::factory()->create();
        $otherClient = Client::factory()->create(['company_id' => $otherCompany->id]);
        
        $data = [
            'title' => 'Invalid Task',
            'client_id' => $otherClient->id,
        ];

        // Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Client does not belong to the specified company');
        $this->taskService->create($this->company, $data);
    }

    /**
     * @test
     * Arrange: Task exists
     * Act: Update task via service
     * Assert: Task is updated with new data
     */
    public function it_updates_a_task(): void
    {
        // Arrange
        $task = Task::factory()->create([
            'client_id' => $this->client->id,
            'title' => 'Original Title',
        ]);
        $data = ['title' => 'Updated Title'];

        // Act
        $updatedTask = $this->taskService->update($task, $data);

        // Assert
        $this->assertEquals('Updated Title', $updatedTask->title);
    }

    /**
     * @test
     * Arrange: Task with pending status exists
     * Act: Mark task as in progress
     * Assert: Task status is updated to in progress
     */
    public function it_marks_task_as_in_progress(): void
    {
        // Arrange
        $task = Task::factory()->create([
            'client_id' => $this->client->id,
            'status' => TaskStatus::Pending,
        ]);

        // Act
        $updatedTask = $this->taskService->markAsInProgress($task);

        // Assert
        $this->assertEquals(TaskStatus::InProgress, $updatedTask->status);
    }

    /**
     * @test
     * Arrange: Task already in progress
     * Act: Mark task as in progress
     * Assert: Task remains unchanged (early return)
     */
    public function it_returns_early_if_task_already_in_progress(): void
    {
        // Arrange
        $task = Task::factory()->create([
            'client_id' => $this->client->id,
            'status' => TaskStatus::InProgress,
        ]);

        // Act
        $updatedTask = $this->taskService->markAsInProgress($task);

        // Assert
        $this->assertEquals(TaskStatus::InProgress, $updatedTask->status);
        $this->assertEquals($task->id, $updatedTask->id);
    }

    /**
     * @test
     * Arrange: Task with in progress status exists
     * Act: Mark task as completed
     * Assert: Task status is updated to completed
     */
    public function it_marks_task_as_completed(): void
    {
        // Arrange
        $task = Task::factory()->create([
            'client_id' => $this->client->id,
            'status' => TaskStatus::InProgress,
        ]);

        // Act
        $updatedTask = $this->taskService->markAsCompleted($task);

        // Assert
        $this->assertEquals(TaskStatus::Completed, $updatedTask->status);
    }

    /**
     * @test
     * Arrange: Task already completed
     * Act: Mark task as completed
     * Assert: Task remains unchanged (early return)
     */
    public function it_returns_early_if_task_already_completed(): void
    {
        // Arrange
        $task = Task::factory()->create([
            'client_id' => $this->client->id,
            'status' => TaskStatus::Completed,
        ]);

        // Act
        $updatedTask = $this->taskService->markAsCompleted($task);

        // Assert
        $this->assertEquals(TaskStatus::Completed, $updatedTask->status);
    }

    /**
     * @test
     * Arrange: Task with active status exists
     * Act: Cancel task
     * Assert: Task status is updated to cancelled
     */
    public function it_cancels_a_task(): void
    {
        // Arrange
        $task = Task::factory()->create([
            'client_id' => $this->client->id,
            'status' => TaskStatus::Pending,
        ]);

        // Act
        $cancelledTask = $this->taskService->cancel($task);

        // Assert
        $this->assertEquals(TaskStatus::Cancelled, $cancelledTask->status);
    }

    /**
     * @test
     * Arrange: Task already cancelled
     * Act: Cancel task
     * Assert: Task remains unchanged (early return)
     */
    public function it_returns_early_if_task_already_cancelled(): void
    {
        // Arrange
        $task = Task::factory()->create([
            'client_id' => $this->client->id,
            'status' => TaskStatus::Cancelled,
        ]);

        // Act
        $cancelledTask = $this->taskService->cancel($task);

        // Assert
        $this->assertEquals(TaskStatus::Cancelled, $cancelledTask->status);
    }

    /**
     * @test
     * Arrange: Task exists
     * Act: Duplicate task
     * Assert: New task is created with same data but new number and pending status
     */
    public function it_duplicates_a_task(): void
    {
        // Arrange
        $originalTask = Task::factory()->create([
            'client_id' => $this->client->id,
            'title' => 'Original Task',
            'status' => TaskStatus::Completed,
        ]);

        // Act
        $duplicatedTask = $this->taskService->duplicate($originalTask);

        // Assert
        $this->assertNotEquals($originalTask->id, $duplicatedTask->id);
        $this->assertNotEquals($originalTask->task_number, $duplicatedTask->task_number);
        $this->assertEquals('Original Task', $duplicatedTask->title);
        $this->assertEquals(TaskStatus::Pending, $duplicatedTask->status);
    }

    /**
     * @test
     * Arrange: Task with timesheets exists
     * Act: Calculate total hours
     * Assert: Returns correct sum of timesheet hours
     */
    public function it_calculates_total_hours_for_a_task(): void
    {
        // Arrange
        $task = Task::factory()->create(['client_id' => $this->client->id]);
        Timesheet::factory()->create(['task_id' => $task->id, 'hours' => 5.5]);
        Timesheet::factory()->create(['task_id' => $task->id, 'hours' => 3.0]);
        Timesheet::factory()->create(['task_id' => $task->id, 'hours' => 2.5]);

        // Act
        $totalHours = $this->taskService->calculateTotalHours($task);

        // Assert
        $this->assertEquals(11.0, $totalHours);
    }

    /**
     * @test
     * Arrange: Task with no timesheets
     * Act: Calculate total hours
     * Assert: Returns 0.0
     */
    public function it_returns_zero_for_task_with_no_timesheets(): void
    {
        // Arrange
        $task = Task::factory()->create(['client_id' => $this->client->id]);

        // Act
        $totalHours = $this->taskService->calculateTotalHours($task);

        // Assert
        $this->assertEquals(0.0, $totalHours);
    }

    /**
     * @test
     * Arrange: Task with estimated hours and timesheets
     * Act: Calculate remaining hours
     * Assert: Returns estimated minus logged hours
     */
    public function it_calculates_remaining_hours(): void
    {
        // Arrange
        $task = Task::factory()->create([
            'client_id' => $this->client->id,
            'estimated_hours' => 10,
        ]);
        Timesheet::factory()->create(['task_id' => $task->id, 'hours' => 3.5]);
        Timesheet::factory()->create(['task_id' => $task->id, 'hours' => 2.5]);

        // Act
        $remainingHours = $this->taskService->calculateRemainingHours($task);

        // Assert
        $this->assertEquals(4.0, $remainingHours);
    }

    /**
     * @test
     * Arrange: Task with no estimated hours
     * Act: Calculate remaining hours
     * Assert: Returns null (early return)
     */
    public function it_returns_null_for_remaining_hours_when_no_estimate(): void
    {
        // Arrange
        $task = Task::factory()->create([
            'client_id' => $this->client->id,
            'estimated_hours' => null,
        ]);
        Timesheet::factory()->create(['task_id' => $task->id, 'hours' => 5.0]);

        // Act
        $remainingHours = $this->taskService->calculateRemainingHours($task);

        // Assert
        $this->assertNull($remainingHours);
    }

    /**
     * @test
     * Arrange: Task with more logged hours than estimated
     * Act: Calculate remaining hours
     * Assert: Returns 0 (not negative)
     */
    public function it_returns_zero_when_logged_hours_exceed_estimated(): void
    {
        // Arrange
        $task = Task::factory()->create([
            'client_id' => $this->client->id,
            'estimated_hours' => 5,
        ]);
        Timesheet::factory()->create(['task_id' => $task->id, 'hours' => 7.0]);

        // Act
        $remainingHours = $this->taskService->calculateRemainingHours($task);

        // Assert
        $this->assertEquals(0.0, $remainingHours);
    }
}
