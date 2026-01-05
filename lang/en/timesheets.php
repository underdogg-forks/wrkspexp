<?php

return [
    'singular' => 'Timesheet',
    'plural' => 'Timesheets',

    'fields' => [
        'timesheet_number' => 'Timesheet Number',
        'task' => 'Task',
        'task_name' => 'Task Name',
        'user' => 'User',
        'started_at' => 'Started At',
        'ended_at' => 'Ended At',
        'hours' => 'Hours',
        'is_billable' => 'Billable',
    ],

    'helpers' => [
        'timesheet_number' => 'Auto-generated timesheet number (TS-YYYY-NNNNNN)',
        'hours' => 'Hours will be calculated automatically when you set start and end times',
    ],

    'filters' => [
        'billable' => 'Billability',
        'billable_only' => 'Billable Only',
        'non_billable' => 'Non-Billable Only',
        'user' => 'Filter by User',
        'task' => 'Filter by Task',
    ],

    'actions' => [
        'start_timer' => 'Start Timer',
        'stop_timer' => 'Stop Timer',
        'duplicate' => 'Duplicate',
    ],

    'notifications' => [
        'created' => 'Timesheet created successfully',
        'updated' => 'Timesheet updated successfully',
        'deleted' => 'Timesheet deleted successfully',
        'timer_started' => 'Timer started successfully',
        'timer_stopped' => 'Timer stopped successfully',
        'duplicated' => 'Timesheet duplicated successfully',
    ],

    'status' => [
        'running' => 'Running',
        'completed' => 'Completed',
    ],
];
