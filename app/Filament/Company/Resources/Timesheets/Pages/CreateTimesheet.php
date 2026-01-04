<?php

namespace App\Filament\Company\Resources\Timesheets\Pages;

use App\Filament\Company\Resources\Timesheets\TimesheetResource;
use App\Models\Timesheet;
use App\Services\TimesheetService;
use Filament\Resources\Pages\CreateRecord;

class CreateTimesheet extends CreateRecord
{
    protected static string $resource = TimesheetResource::class;

    protected function handleRecordCreation(array $data): Timesheet
    {
        $service = app(TimesheetService::class);
        $task = \App\Models\Task::findOrFail($data['task_id']);
        $user = auth()->user();
        
        return $service->create($task, $user, $data);
    }
}
