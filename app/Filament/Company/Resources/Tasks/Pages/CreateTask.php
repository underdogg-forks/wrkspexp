<?php

namespace App\Filament\Company\Resources\Tasks\Pages;

use App\Filament\Company\Resources\Tasks\TaskResource;
use App\Services\TaskService;
use Filament\Resources\Pages\CreateRecord;

class CreateTask extends CreateRecord
{
    protected static string $resource = TaskResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $service = app(TaskService::class);
        $company = filament()->getTenant();
        $task = $service->create($company, $data);
        
        // Return empty array to prevent default creation, as service already created the record
        $this->record = $task;
        $this->halt();
        
        return [];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
