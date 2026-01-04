<?php

namespace App\Filament\Company\Resources\Tasks\Tables;

use App\Enums\TaskStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TasksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('task_number')
                    ->label(trans('tasks.task_number'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('title')
                    ->label(trans('tasks.title'))
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                TextColumn::make('client.relation.name')
                    ->label(trans('tasks.client'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('project.name')
                    ->label(trans('tasks.project'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                BadgeColumn::make('status')
                    ->label(trans('tasks.status'))
                    ->enum(TaskStatus::class)
                    ->colors(fn (TaskStatus $state): string => $state->color()),

                TextColumn::make('estimated_hours')
                    ->label(trans('tasks.estimated_hours'))
                    ->suffix(' hrs')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('timesheets_sum_hours')
                    ->label(trans('tasks.total_hours'))
                    ->suffix(' hrs')
                    ->sortable()
                    ->toggleable()
                    ->default(0),

                TextColumn::make('due_at')
                    ->label(trans('tasks.due_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(trans('tasks.status'))
                    ->options(TaskStatus::class),

                SelectFilter::make('project_id')
                    ->label(trans('tasks.project'))
                    ->relationship('project', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('mark_in_progress')
                    ->label(trans('tasks.mark_in_progress'))
                    ->icon('heroicon-o-play')
                    ->visible(fn ($record) => $record->status === TaskStatus::Pending)
                    ->action(function ($record, $action) {
                        $service = app(\App\Services\TaskService::class);
                        $service->markAsInProgress($record);
                        $action->success();
                    }),
                Action::make('mark_completed')
                    ->label(trans('tasks.mark_completed'))
                    ->icon('heroicon-o-check-circle')
                    ->visible(fn ($record) => $record->status !== TaskStatus::Completed)
                    ->action(function ($record, $action) {
                        $service = app(\App\Services\TaskService::class);
                        $service->markAsCompleted($record);
                        $action->success();
                    }),
                Action::make('duplicate')
                    ->label(trans('tasks.duplicate_task'))
                    ->icon('heroicon-o-document-duplicate')
                    ->action(function ($record, $action) {
                        $service = app(\App\Services\TaskService::class);
                        $newTask = $service->duplicate($record);
                        $action->success();
                        return redirect()->route('filament.company.resources.tasks.edit', ['record' => $newTask]);
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('task_number', 'desc');
    }
}
