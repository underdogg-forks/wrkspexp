<?php

namespace Tests\Feature\Services;

use App\Models\Task;
use App\Models\Timesheet;
use App\Models\User;
use App\Services\TimesheetService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimesheetServiceTest extends TestCase
{
    use RefreshDatabase;

    private TimesheetService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(TimesheetService::class);
    }

    /** @test */
    public function it_creates_a_timesheet_with_generated_number()
    {
        // Arrange
        $task = Task::factory()->create();
        $user = User::factory()->create();
        $data = [
            'started_at' => now(),
            'is_billable' => true,
        ];

        // Act
        $timesheet = $this->service->create($task, $user, $data);

        // Assert
        $this->assertInstanceOf(Timesheet::class, $timesheet);
        $this->assertEquals($task->id, $timesheet->task_id);
        $this->assertEquals($user->id, $timesheet->user_id);
        $this->assertTrue($timesheet->is_billable);
        $this->assertStringStartsWith('TS-' . date('Y') . '-', $timesheet->timesheet_number);
    }

    /** @test */
    public function it_creates_a_timesheet_with_custom_number()
    {
        // Arrange
        $task = Task::factory()->create();
        $user = User::factory()->create();
        $customNumber = 'TS-2026-999999';
        $data = [
            'timesheet_number' => $customNumber,
            'started_at' => now(),
        ];

        // Act
        $timesheet = $this->service->create($task, $user, $data);

        // Assert
        $this->assertEquals($customNumber, $timesheet->timesheet_number);
    }

    /** @test */
    public function it_calculates_hours_when_both_times_provided()
    {
        // Arrange
        $task = Task::factory()->create();
        $user = User::factory()->create();
        $startedAt = now();
        $endedAt = $startedAt->copy()->addHours(3)->addMinutes(30);
        $data = [
            'started_at' => $startedAt,
            'ended_at' => $endedAt,
        ];

        // Act
        $timesheet = $this->service->create($task, $user, $data);

        // Assert
        $this->assertEquals(3.5, $timesheet->hours);
    }

    /** @test */
    public function it_throws_exception_when_task_is_missing()
    {
        // Arrange
        $user = User::factory()->create();
        $data = ['started_at' => now()];

        // Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Task is required');

        // Act
        $this->service->create(null, $user, $data);
    }

    /** @test */
    public function it_throws_exception_when_user_is_missing()
    {
        // Arrange
        $task = Task::factory()->create();
        $data = ['started_at' => now()];

        // Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('User is required');

        // Act
        $this->service->create($task, null, $data);
    }

    /** @test */
    public function it_updates_a_timesheet()
    {
        // Arrange
        $timesheet = Timesheet::factory()->create(['is_billable' => true]);
        $data = ['is_billable' => false];

        // Act
        $updated = $this->service->update($timesheet, $data);

        // Assert
        $this->assertFalse($updated->is_billable);
        $this->assertEquals($timesheet->id, $updated->id);
    }

    /** @test */
    public function it_recalculates_hours_when_updating_times()
    {
        // Arrange
        $timesheet = Timesheet::factory()->create([
            'started_at' => now()->subHours(2),
            'ended_at' => now(),
            'hours' => 2.0,
        ]);
        $newEndedAt = $timesheet->started_at->copy()->addHours(5);
        $data = ['ended_at' => $newEndedAt];

        // Act
        $updated = $this->service->update($timesheet, $data);

        // Assert
        $this->assertEquals(5.0, $updated->hours);
    }

    /** @test */
    public function it_deletes_a_timesheet()
    {
        // Arrange
        $timesheet = Timesheet::factory()->create();

        // Act
        $result = $this->service->delete($timesheet);

        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseMissing('timesheets', ['id' => $timesheet->id]);
    }

    /** @test */
    public function it_starts_a_timer()
    {
        // Arrange
        $task = Task::factory()->create();
        $user = User::factory()->create();

        // Act
        $timesheet = $this->service->startTimer($task, $user);

        // Assert
        $this->assertInstanceOf(Timesheet::class, $timesheet);
        $this->assertNotNull($timesheet->started_at);
        $this->assertNull($timesheet->ended_at);
        $this->assertNull($timesheet->hours);
    }

    /** @test */
    public function it_stops_a_timer()
    {
        // Arrange
        $timesheet = Timesheet::factory()->running()->create([
            'started_at' => now()->subHours(2),
        ]);

        // Act
        $stopped = $this->service->stopTimer($timesheet);

        // Assert
        $this->assertNotNull($stopped->ended_at);
        $this->assertNotNull($stopped->hours);
        $this->assertGreaterThan(0, $stopped->hours);
    }

    /** @test */
    public function it_does_not_stop_an_already_stopped_timer()
    {
        // Arrange
        $originalEndedAt = now()->subHour();
        $timesheet = Timesheet::factory()->create([
            'started_at' => now()->subHours(3),
            'ended_at' => $originalEndedAt,
            'hours' => 2.0,
        ]);

        // Act
        $result = $this->service->stopTimer($timesheet);

        // Assert
        $this->assertEquals($originalEndedAt->timestamp, $result->ended_at->timestamp);
        $this->assertEquals(2.0, $result->hours);
    }

    /** @test */
    public function it_duplicates_a_timesheet()
    {
        // Arrange
        $original = Timesheet::factory()->create();

        // Act
        $duplicate = $this->service->duplicate($original);

        // Assert
        $this->assertNotEquals($original->id, $duplicate->id);
        $this->assertNotEquals($original->timesheet_number, $duplicate->timesheet_number);
        $this->assertEquals($original->task_id, $duplicate->task_id);
        $this->assertEquals($original->user_id, $duplicate->user_id);
        $this->assertEquals($original->hours, $duplicate->hours);
        $this->assertEquals($original->is_billable, $duplicate->is_billable);
    }

    /** @test */
    public function it_calculates_total_hours_for_a_task()
    {
        // Arrange
        $task = Task::factory()->create();
        Timesheet::factory()->create(['task_id' => $task->id, 'hours' => 2.5]);
        Timesheet::factory()->create(['task_id' => $task->id, 'hours' => 3.5]);
        Timesheet::factory()->create(['task_id' => $task->id, 'hours' => 1.0]);

        // Act
        $totalHours = $this->service->calculateTotalHours($task);

        // Assert
        $this->assertEquals(7.0, $totalHours);
    }

    /** @test */
    public function it_calculates_billable_hours_for_a_task()
    {
        // Arrange
        $task = Task::factory()->create();
        Timesheet::factory()->billable()->create(['task_id' => $task->id, 'hours' => 2.5]);
        Timesheet::factory()->billable()->create(['task_id' => $task->id, 'hours' => 3.5]);
        Timesheet::factory()->nonBillable()->create(['task_id' => $task->id, 'hours' => 1.0]);

        // Act
        $billableHours = $this->service->calculateBillableHours($task);

        // Assert
        $this->assertEquals(6.0, $billableHours);
    }

    /** @test */
    public function it_calculates_non_billable_hours_for_a_task()
    {
        // Arrange
        $task = Task::factory()->create();
        Timesheet::factory()->billable()->create(['task_id' => $task->id, 'hours' => 2.5]);
        Timesheet::factory()->nonBillable()->create(['task_id' => $task->id, 'hours' => 1.5]);
        Timesheet::factory()->nonBillable()->create(['task_id' => $task->id, 'hours' => 0.5]);

        // Act
        $nonBillableHours = $this->service->calculateNonBillableHours($task);

        // Assert
        $this->assertEquals(2.0, $nonBillableHours);
    }

    /** @test */
    public function it_gets_active_timesheets_for_a_user()
    {
        // Arrange
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        
        Timesheet::factory()->running()->create(['user_id' => $user->id]);
        Timesheet::factory()->running()->create(['user_id' => $user->id]);
        Timesheet::factory()->create(['user_id' => $user->id]); // completed
        Timesheet::factory()->running()->create(['user_id' => $otherUser->id]); // other user

        // Act
        $activeTimesheets = $this->service->getActiveTimesheets($user);

        // Assert
        $this->assertCount(2, $activeTimesheets);
        $activeTimesheets->each(function ($timesheet) use ($user) {
            $this->assertEquals($user->id, $timesheet->user_id);
            $this->assertNull($timesheet->ended_at);
        });
    }

    /** @test */
    public function it_generates_sequential_timesheet_numbers()
    {
        // Arrange
        $task = Task::factory()->create();
        $user = User::factory()->create();
        $year = date('Y');

        // Act
        $timesheet1 = $this->service->create($task, $user, ['started_at' => now()]);
        $timesheet2 = $this->service->create($task, $user, ['started_at' => now()]);
        $timesheet3 = $this->service->create($task, $user, ['started_at' => now()]);

        // Assert
        $this->assertStringStartsWith("TS-{$year}-", $timesheet1->timesheet_number);
        $this->assertStringStartsWith("TS-{$year}-", $timesheet2->timesheet_number);
        $this->assertStringStartsWith("TS-{$year}-", $timesheet3->timesheet_number);
        
        // Extract numbers and verify they're sequential
        $num1 = (int) str_replace("TS-{$year}-", '', $timesheet1->timesheet_number);
        $num2 = (int) str_replace("TS-{$year}-", '', $timesheet2->timesheet_number);
        $num3 = (int) str_replace("TS-{$year}-", '', $timesheet3->timesheet_number);
        
        $this->assertEquals($num1 + 1, $num2);
        $this->assertEquals($num2 + 1, $num3);
    }
}
