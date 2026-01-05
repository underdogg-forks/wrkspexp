<?php

namespace App\Filament\Company\Resources\Expenses\Pages;

use App\Filament\Company\Resources\Expenses\ExpenseResource;
use App\Services\ExpenseService;
use Filament\Resources\Pages\CreateRecord;

class CreateExpense extends CreateRecord
{
    protected static string $resource = ExpenseResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $service = app(ExpenseService::class);
        $company = filament()->getTenant();
        $expense = $service->create($company, $data);
        
        $this->record = $expense;
        $this->halt();
        
        return [];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
