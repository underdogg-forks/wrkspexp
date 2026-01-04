<?php

namespace App\Filament\Company\Resources/Companies\Pages;

use App\Filament\Company\Resources\Companies\CompanyResource;
use App\Filament\Company\Resources\Companies\Schemas\CompanyForm;
use App\Services\CompanyService;
use Filament\Resources\Pages\CreateRecord;

class CreateCompany extends CreateRecord
{
    protected static string $resource = CompanyResource::class;

    public function form(\Filament\Forms\Form $form): \Filament\Forms\Form
    {
        return CompanyForm::schema($form);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $service = app(CompanyService::class);
        
        // Generate company number if not provided
        if (empty($data['company_number'])) {
            $data['company_number'] = $service->generateCompanyNumber();
        }

        return $data;
    }

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        $service = app(CompanyService::class);
        return $service->create($data);
    }
}
