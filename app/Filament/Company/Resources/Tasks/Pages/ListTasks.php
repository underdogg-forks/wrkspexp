<?php

namespace App\Filament\Company\Resources\Tasks\Pages;

use App\Filament\Company\Resources\Tasks\TaskResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTasks extends ListRecords
{
    protected static string $resource = TaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label(trans('tasks.create_task'))
                ->modalWidth('5xl')
                ->modalHeading(trans('tasks.create_task'))
                ->using(function (array $data): \App\Models\Task {
                    $service = app(\App\Services\TaskService::class);
                    $company = filament()->getTenant();
                    return $service->create($company, $data);
                }),
        ];
    }
}
