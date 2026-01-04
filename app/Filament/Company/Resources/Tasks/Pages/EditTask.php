<?php

namespace App\Filament\Company\Resources\Tasks\Pages;

use App\Filament\Company\Resources\Tasks\TaskResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTask extends EditRecord
{
    protected static string $resource = TaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\Action::make('markAsInProgress')
                ->label(trans('tasks.mark_in_progress'))
                ->icon('heroicon-o-play')
                ->visible(fn ($record) => $record->status->value === \App\Enums\TaskStatus::Pending->value)
                ->action(function ($record) {
                    $service = app(\App\Services\TaskService::class);
                    $service->markAsInProgress($record);
                    $this->refreshFormData(['status']);
                }),
            Actions\Action::make('markAsCompleted')
                ->label(trans('tasks.mark_completed'))
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn ($record) => $record->status->value !== \App\Enums\TaskStatus::Completed->value)
                ->action(function ($record) {
                    $service = app(\App\Services\TaskService::class);
                    $service->markAsCompleted($record);
                    $this->refreshFormData(['status']);
                }),
            Actions\Action::make('cancel')
                ->label(trans('tasks.cancel_task'))
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn ($record) => !in_array($record->status->value, [
                    \App\Enums\TaskStatus::Completed->value,
                    \App\Enums\TaskStatus::Cancelled->value
                ]))
                ->action(function ($record) {
                    $service = app(\App\Services\TaskService::class);
                    $service->cancel($record);
                    $this->refreshFormData(['status']);
                }),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $service = app(\App\Services\TaskService::class);
        $service->update($this->record, $data);
        
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
