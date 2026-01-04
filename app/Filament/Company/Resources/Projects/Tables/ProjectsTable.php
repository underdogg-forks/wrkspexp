<?php

namespace App\Filament\Company\Resources\Projects\Tables;

use App\Enums\ProjectStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProjectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('project_number')
                    ->label(trans('projects.project_number'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label(trans('projects.name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('client.relation.name')
                    ->label(trans('projects.client'))
                    ->searchable()
                    ->sortable(),

                BadgeColumn::make('status')
                    ->label(trans('projects.status'))
                    ->enum(ProjectStatus::class)
                    ->colors([
                        'success' => ProjectStatus::Active->value,
                        'warning' => ProjectStatus::OnHold->value,
                        'info' => ProjectStatus::Completed->value,
                        'danger' => ProjectStatus::Cancelled->value,
                    ]),

                TextColumn::make('budget')
                    ->label(trans('projects.budget'))
                    ->money('USD')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('started_at')
                    ->label(trans('projects.started_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('ended_at')
                    ->label(trans('projects.ended_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('completion')
                    ->label(trans('projects.completion'))
                    ->getStateUsing(function ($record) {
                        $service = app(\App\Services\ProjectService::class);
                        return $service->calculateCompletionPercentage($record) . '%';
                    })
                    ->sortable(false),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(trans('projects.status'))
                    ->options(ProjectStatus::class),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('duplicate')
                    ->label(trans('projects.duplicate'))
                    ->icon('heroicon-o-document-duplicate')
                    ->action(function ($record, $action) {
                        $service = app(\App\Services\ProjectService::class);
                        $newProject = $service->duplicate($record);
                        $action->success();
                        return redirect()->route('filament.company.resources.projects.edit', ['record' => $newProject]);
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('project_number', 'desc');
    }
}
