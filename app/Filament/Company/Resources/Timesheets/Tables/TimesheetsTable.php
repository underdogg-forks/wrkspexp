<?php

namespace App\Filament\Company\Resources\Timesheets\Tables;

use App\Models\Timesheet;
use App\Services\TimesheetService;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TimesheetsTable
{
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('timesheet_number')
                    ->label(trans('timesheets.fields.timesheet_number'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('task.task_number')
                    ->label(trans('timesheets.fields.task'))
                    ->searchable()
                    ->sortable()
                    ->url(fn (Timesheet $record) => route('filament.company.resources.tasks.edit', $record->task_id)),

                TextColumn::make('task.name')
                    ->label(trans('timesheets.fields.task_name'))
                    ->searchable()
                    ->limit(30),

                TextColumn::make('user.name')
                    ->label(trans('timesheets.fields.user'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('started_at')
                    ->label(trans('timesheets.fields.started_at'))
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('ended_at')
                    ->label(trans('timesheets.fields.ended_at'))
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Running'),

                TextColumn::make('hours')
                    ->label(trans('timesheets.fields.hours'))
                    ->numeric(2)
                    ->suffix(' hrs')
                    ->sortable()
                    ->alignEnd(),

                IconColumn::make('is_billable')
                    ->label(trans('timesheets.fields.is_billable'))
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('is_billable')
                    ->label(trans('timesheets.filters.billable'))
                    ->options([
                        1 => trans('timesheets.filters.billable_only'),
                        0 => trans('timesheets.filters.non_billable'),
                    ]),

                SelectFilter::make('user_id')
                    ->label(trans('timesheets.filters.user'))
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('task_id')
                    ->label(trans('timesheets.filters.task'))
                    ->relationship('task', 'task_number')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('started_at', 'desc');
    }
}
