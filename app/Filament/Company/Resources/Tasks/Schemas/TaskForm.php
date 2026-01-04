<?php

namespace App\Filament\Company\Resources\Tasks\Schemas;

use App\Enums\TaskStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Section;
use Filament\Schemas\Schema;

class TaskForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(trans('tasks.task_details'))
                    ->schema([
                        TextInput::make('task_number')
                            ->label(trans('tasks.task_number'))
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50),

                        TextInput::make('title')
                            ->label(trans('tasks.title'))
                            ->required()
                            ->maxLength(255),

                        Select::make('client_id')
                            ->label(trans('tasks.client'))
                            ->relationship(
                                'client',
                                'name',
                                fn ($query) => $query->where('company_id', filament()->getTenant()->id),
                            )
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('project_id')
                            ->label(trans('tasks.project'))
                            ->relationship(
                                'project',
                                'name',
                                fn ($query) => $query->where('company_id', filament()->getTenant()->id),
                            )
                            ->searchable()
                            ->preload()
                            ->nullable(),

                        Select::make('status')
                            ->label(trans('tasks.status'))
                            ->options(TaskStatus::class)
                            ->required()
                            ->default(TaskStatus::Pending->value)
                            ->enum(TaskStatus::class),

                        TextInput::make('estimated_hours')
                            ->label(trans('tasks.estimated_hours'))
                            ->numeric()
                            ->suffix(trans('tasks.hours'))
                            ->nullable(),

                        DateTimePicker::make('due_at')
                            ->label(trans('tasks.due_at'))
                            ->nullable(),

                        Textarea::make('description')
                            ->label(trans('tasks.description'))
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
