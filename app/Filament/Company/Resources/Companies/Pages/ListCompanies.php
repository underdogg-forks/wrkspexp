<?php

namespace App\Filament\Company\Resources\Companies\Pages;

use App\Filament\Company\Resources\Companies\CompanyResource;
use App\Filament\Company\Resources\Companies\Schemas\CompanyForm;
use App\Filament\Company\Resources\Companies\Tables\CompaniesTable;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;

class ListCompanies extends ListRecords
{
    protected static string $resource = CompanyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->form(fn ($form) => CompanyForm::schema($form))
                ->using(function (array $data) {
                    $service = app(\App\Services\CompanyService::class);
                    return $service->create($data);
                }),
        ];
    }

    public function table(Table $table): Table
    {
        return CompaniesTable::table($table);
    }
}
