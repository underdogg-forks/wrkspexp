<?php

namespace App\Filament\Company\Resources\Invoices\Pages;

use App\Filament\Company\Resources\Invoices\InvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInvoice extends EditRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\Action::make('markAsSent')
                ->label(trans('invoices.mark_as_sent'))
                ->icon('heroicon-o-paper-airplane')
                ->visible(fn ($record) => $record->status->value === \App\Enums\InvoiceStatus::Draft->value)
                ->action(function ($record) {
                    $service = app(\App\Services\InvoiceService::class);
                    $service->markAsSent($record);
                    $this->refreshFormData(['status', 'issued_at']);
                }),
            Actions\Action::make('markAsPaid')
                ->label(trans('invoices.mark_as_paid'))
                ->icon('heroicon-o-check-circle')
                ->visible(fn ($record) => in_array($record->status->value, [
                    \App\Enums\InvoiceStatus::Sent->value,
                    \App\Enums\InvoiceStatus::Overdue->value
                ]))
                ->action(function ($record) {
                    $service = app(\App\Services\InvoiceService::class);
                    $service->markAsPaid($record);
                    $this->refreshFormData(['status']);
                }),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $service = app(\App\Services\InvoiceService::class);
        $service->update($this->record, $data);
        
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
