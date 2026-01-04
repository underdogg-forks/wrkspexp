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
                ->after(function ($record) {
                    // Update invoice status after deletion
                    $service = app(\App\Services\PaymentService::class);
                    $service->delete($record);
                }),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $service = app(\App\Services\PaymentService::class);
        $service->update($this->record, $data);
        
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
