<?php

namespace App\Filament\Company\Resources\Payments\Pages;

use App\Filament\Company\Resources\Payments\PaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPayment extends EditRecord
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(function ($record) {
                    // Store invoice for later update
                    $this->invoiceToUpdate = $record->invoice;
                })
                ->after(function () {
                    // Update invoice status after deletion (record is already deleted by DeleteAction)
                    if (isset($this->invoiceToUpdate)) {
                        $service = app(\App\Services\PaymentService::class);
                        $service->updateInvoiceStatus($this->invoiceToUpdate);
                    }
                }),
        ];
    }

    protected $invoiceToUpdate;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Service layer handles the update
        $service = app(\App\Services\PaymentService::class);
        $service->update($this->record, $data);
        
        // Halt to prevent duplicate update by Filament
        $this->halt();
        
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
