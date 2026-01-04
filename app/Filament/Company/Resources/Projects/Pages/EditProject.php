<?php

namespace App\Filament\Company\Resources\Projects\Pages;

use App\Filament\Company\Resources\Projects\ProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProject extends EditRecord
{
    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\Action::make('markAsCompleted')
                ->label(trans('projects.mark_as_completed'))
                ->icon('heroicon-o-check-circle')
                ->visible(fn ($record) => $record->status->value === \App\Enums\ProjectStatus::Active->value)
                ->action(function ($record) {
                    $service = app(\App\Services\ProjectService::class);
                    $service->markAsCompleted($record);
                    $this->refreshFormData(['status', 'ended_at']);
                }),
            Actions\Action::make('putOnHold')
                ->label(trans('projects.put_on_hold'))
                ->icon('heroicon-o-pause-circle')
                ->visible(fn ($record) => $record->status->value === \App\Enums\ProjectStatus::Active->value)
                ->action(function ($record) {
                    $service = app(\App\Services\ProjectService::class);
                    $service->putOnHold($record);
                    $this->refreshFormData(['status']);
                }),
            Actions\Action::make('cancel')
                ->label(trans('projects.cancel'))
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn ($record) => in_array($record->status->value, [
                    \App\Enums\ProjectStatus::Active->value,
                    \App\Enums\ProjectStatus::OnHold->value
                ]))
                ->action(function ($record) {
                    $service = app(\App\Services\ProjectService::class);
                    $service->cancel($record);
                    $this->refreshFormData(['status', 'ended_at']);
                }),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $service = app(\App\Services\ProjectService::class);
        $service->update($this->record, $data);
        
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
