<?php

namespace App\Filament\Company\Resources\Timesheets\Schemas;

use App\Models\Task;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;

class TimesheetForm
{
    public static function schema(): array
    {
        return [
            Section::make('Timesheet Information')
                ->schema([
                    Select::make('task_id')
                        ->label(trans('timesheets.fields.task'))
                        ->relationship('task', 'task_number')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->getOptionLabelFromRecordUsing(fn (Task $record) => 
                            "{$record->task_number} - {$record->name}"
                        ),

                    TextInput::make('timesheet_number')
                        ->label(trans('timesheets.fields.timesheet_number'))
                        ->disabled()
                        ->dehydrated(false)
                        ->helperText(trans('timesheets.helpers.timesheet_number')),

                    DateTimePicker::make('started_at')
                        ->label(trans('timesheets.fields.started_at'))
                        ->required()
                        ->seconds(false)
                        ->native(false),

                    DateTimePicker::make('ended_at')
                        ->label(trans('timesheets.fields.ended_at'))
                        ->seconds(false)
                        ->native(false)
                        ->after('started_at'),

                    TextInput::make('hours')
                        ->label(trans('timesheets.fields.hours'))
                        ->numeric()
                        ->step(0.25)
                        ->suffix('hours')
                        ->helperText(trans('timesheets.helpers.hours'))
                        ->disabled()
                        ->dehydrated(false),

                    Toggle::make('is_billable')
                        ->label(trans('timesheets.fields.is_billable'))
                        ->default(true)
                        ->inline(false),
                ]),
        ];
    }
}
