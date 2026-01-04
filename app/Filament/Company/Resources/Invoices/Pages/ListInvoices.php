<?php

namespace App\Filament\Company\Resources\Invoices\Pages;

use App\Filament\Company\Resources\Invoices\InvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInvoices extends ListRecords
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label(trans('invoices.create_invoice'))
                ->modalWidth('7xl')
                ->modalHeading(trans('invoices.create_invoice'))
                ->using(function (array $data): \App\Models\Invoice {
                    $service = app(\App\Services\InvoiceService::class);
                    $company = filament()->getTenant();
                    return $service->create($company, $data);
                }),
        ];
    }
}
