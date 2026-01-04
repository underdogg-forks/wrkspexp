<?php

namespace App\Filament\Company\Resources\Projects\Pages;

use App\Filament\Company\Resources\Projects\ProjectResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProject extends CreateRecord
{
    protected static string $resource = ProjectResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $service = app(\App\Services\ProjectService::class);
        $company = filament()->getTenant();
        
        // Generate project number if not provided
        if (empty($data['project_number'])) {
            $project = $service->create($company, $data);
            $data['project_number'] = $project->project_number;
        }
        
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
