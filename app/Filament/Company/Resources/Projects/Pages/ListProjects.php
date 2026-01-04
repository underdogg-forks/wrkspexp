<?php

namespace App\Filament\Company\Resources\Projects\Pages;

use App\Filament\Company\Resources\Projects\ProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProjects extends ListRecords
{
    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label(trans('projects.create_project'))
                ->modalWidth('5xl')
                ->modalHeading(trans('projects.create_project'))
                ->using(function (array $data): \App\Models\Project {
                    $service = app(\App\Services\ProjectService::class);
                    $company = filament()->getTenant();
                    return $service->create($company, $data);
                }),
        ];
    }
}
