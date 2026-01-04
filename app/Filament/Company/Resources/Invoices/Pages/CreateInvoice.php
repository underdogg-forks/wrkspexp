<?php

namespace App\Filament\Company\Resources\Invoices\Pages;

use App\Filament\Company\Resources\Invoices\InvoiceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $service = app(\App\Services\InvoiceService::class);
        $company = filament()->getTenant();
        
        // Generate invoice number if not provided
        if (empty($data['invoice_number'])) {
            $invoice = $service->create($company, $data);
            $data['invoice_number'] = $invoice->invoice_number;
        }
        
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
