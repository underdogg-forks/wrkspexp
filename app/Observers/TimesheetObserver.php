<?php

namespace App\Observers;

use App\Models\Timesheet;
use Carbon\Carbon;

/**
 * TimesheetObserver - Handles lifecycle events for timesheet records
 * 
 * Implements the Observer pattern for automatic business logic execution
 * during model lifecycle events
 */
class TimesheetObserver
{
    /**
     * Handle the Timesheet "creating" event.
     */
    public function creating(Timesheet $timesheet): void
    {
        // Calculate hours if both times are set
        if ($timesheet->started_at && $timesheet->ended_at && !$timesheet->hours) {
            $start = Carbon::parse($timesheet->started_at);
            $end = Carbon::parse($timesheet->ended_at);
            $timesheet->hours = round($start->diffInMinutes($end) / 60, 2);
        }
    }

    /**
     * Handle the Timesheet "updating" event.
     */
    public function updating(Timesheet $timesheet): void
    {
        // Recalculate hours if times changed
        if ($timesheet->isDirty(['started_at', 'ended_at'])) {
            if ($timesheet->started_at && $timesheet->ended_at) {
                $start = Carbon::parse($timesheet->started_at);
                $end = Carbon::parse($timesheet->ended_at);
                $timesheet->hours = round($start->diffInMinutes($end) / 60, 2);
            }
        }
    }

    /**
     * Handle the Timesheet "created" event.
     */
    public function created(Timesheet $timesheet): void
    {
        // Log activity or trigger events here if needed
    }

    /**
     * Handle the Timesheet "updated" event.
     */
    public function updated(Timesheet $timesheet): void
    {
        // Log activity or trigger events here if needed
    }

    /**
     * Handle the Timesheet "deleted" event.
     */
    public function deleted(Timesheet $timesheet): void
    {
        // Log activity or trigger events here if needed
    }
}
