<?php

namespace App\Filament\Company\Resources\Quotes\Pages;

use App\Filament\Company\Resources\Quotes\QuoteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditQuote extends EditRecord
{
    protected static string $resource = QuoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\Action::make('send')
                ->label(trans('quotes.send_quote'))
                ->icon('heroicon-o-paper-airplane')
                ->visible(fn ($record) => $record->status->value === \App\Enums\QuoteStatus::Draft->value)
                ->action(function ($record) {
                    $service = app(\App\Services\QuoteService::class);
                    $service->send($record);
                    $this->refreshFormData(['status', 'issued_at']);
                }),
            Actions\Action::make('accept')
                ->label(trans('quotes.mark_accepted'))
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn ($record) => $record->status->value === \App\Enums\QuoteStatus::Sent->value)
                ->action(function ($record) {
                    $service = app(\App\Services\QuoteService::class);
                    $service->accept($record);
                    $this->refreshFormData(['status']);
                }),
            Actions\Action::make('decline')
                ->label(trans('quotes.mark_declined'))
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn ($record) => $record->status->value === \App\Enums\QuoteStatus::Sent->value)
                ->action(function ($record) {
                    $service = app(\App\Services\QuoteService::class);
                    $service->decline($record);
                    $this->refreshFormData(['status']);
                }),
            Actions\Action::make('convertToInvoice')
                ->label(trans('quotes.convert_to_invoice'))
                ->icon('heroicon-o-document-check')
                ->color('info')
                ->visible(fn ($record) => $record->status->value === \App\Enums\QuoteStatus::Accepted->value)
                ->action(function ($record) {
                    $service = app(\App\Services\QuoteService::class);
                    $invoice = $service->convertToInvoice($record);
                    return redirect()->route('filament.company.resources.invoices.edit', ['record' => $invoice]);
                }),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Service layer handles the update
        $service = app(\App\Services\QuoteService::class);
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
