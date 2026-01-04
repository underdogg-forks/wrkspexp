<?php

namespace App\Filament\Company\Resources\Timesheets\Pages;

use App\Filament\Company\Resources\Timesheets\TimesheetResource;
use App\Models\Timesheet;
use App\Services\TimesheetService;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Actions\Action;

class ListTimesheets extends ListRecords
{
    protected static string $resource = TimesheetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->using(function (array $data): Timesheet {
                    $service = app(TimesheetService::class);
                    $task = \App\Models\Task::findOrFail($data['task_id']);
                    $user = auth()->user();
                    
                    return $service->create($task, $user, $data);
                }),
        ];
    }
}
