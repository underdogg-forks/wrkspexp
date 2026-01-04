<?php

namespace App\Filament\Company\Resources\Timesheets\Pages;

use App\Filament\Company\Resources\Timesheets\TimesheetResource;
use App\Models\Timesheet;
use App\Services\TimesheetService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Exceptions\Halt;

class EditTimesheet extends EditRecord
{
    protected static string $resource = TimesheetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('stop_timer')
                ->label(trans('timesheets.actions.stop_timer'))
                ->icon('heroicon-o-pause')
                ->visible(fn (Timesheet $record) => !$record->ended_at)
                ->action(function (Timesheet $record) {
                    $service = app(TimesheetService::class);
                    $service->stopTimer($record);
                    $this->refreshFormData(['ended_at', 'hours']);
                }),

            Actions\Action::make('duplicate')
                ->label(trans('timesheets.actions.duplicate'))
                ->icon('heroicon-o-document-duplicate')
                ->action(function (Timesheet $record) {
                    $service = app(TimesheetService::class);
                    $newTimesheet = $service->duplicate($record);
                    $this->redirect(static::getResource()::getUrl('edit', ['record' => $newTimesheet->id]));
                }),

            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $service = app(TimesheetService::class);
        $service->update($this->record, $data);
        
        // Prevent Filament's default save since service already saved
        throw new Halt();
    }
}
