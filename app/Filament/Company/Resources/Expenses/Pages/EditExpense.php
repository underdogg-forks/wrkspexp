<?php

namespace App\Filament\Company\Resources\Expenses\Pages;

use App\Filament\Company\Resources\Expenses\ExpenseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditExpense extends EditRecord
{
    protected static string $resource = ExpenseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\Action::make('submit')
                ->label(trans('expenses.submit_for_approval'))
                ->icon('heroicon-o-paper-airplane')
                ->visible(fn ($record) => $record->status->value === \App\Enums\ExpenseStatus::Draft->value)
                ->action(function ($record) {
                    $service = app(\App\Services\ExpenseService::class);
                    $service->submit($record);
                    $this->refreshFormData(['status']);
                }),
            Actions\Action::make('approve')
                ->label(trans('expenses.approve'))
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn ($record) => $record->status->value === \App\Enums\ExpenseStatus::Pending->value)
                ->action(function ($record) {
                    $service = app(\App\Services\ExpenseService::class);
                    $service->approve($record);
                    $this->refreshFormData(['status']);
                }),
            Actions\Action::make('reject')
                ->label(trans('expenses.reject'))
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn ($record) => $record->status->value === \App\Enums\ExpenseStatus::Pending->value)
                ->action(function ($record) {
                    $service = app(\App\Services\ExpenseService::class);
                    $service->reject($record);
                    $this->refreshFormData(['status']);
                }),
            Actions\Action::make('markAsPaid')
                ->label(trans('expenses.mark_as_paid'))
                ->icon('heroicon-o-banknotes')
                ->visible(fn ($record) => $record->status->value === \App\Enums\ExpenseStatus::Approved->value)
                ->action(function ($record) {
                    $service = app(\App\Services\ExpenseService::class);
                    $service->markAsPaid($record);
                    $this->refreshFormData(['status']);
                }),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $service = app(\App\Services\ExpenseService::class);
        $service->update($this->record, $data);
        
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
