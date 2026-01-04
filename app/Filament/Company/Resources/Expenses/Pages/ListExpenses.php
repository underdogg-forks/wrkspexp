<?php

namespace App\Filament\Company\Resources\Expenses\Pages;

use App\Filament\Company\Resources\Expenses\ExpenseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListExpenses extends ListRecords
{
    protected static string $resource = ExpenseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label(trans('expenses.create_expense'))
                ->modalWidth('5xl')
                ->modalHeading(trans('expenses.create_expense'))
                ->using(function (array $data): \App\Models\Expense {
                    $service = app(\App\Services\ExpenseService::class);
                    $company = filament()->getTenant();
                    return $service->create($company, $data);
                }),
        ];
    }
}
