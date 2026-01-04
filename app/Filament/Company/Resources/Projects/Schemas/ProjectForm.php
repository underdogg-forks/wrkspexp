<?php

namespace App\Filament\Company\Resources\Projects\Schemas;

use App\Enums\ProjectStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Section;
use Filament\Schemas\Schema;

class ProjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(trans('projects.project_details'))
                    ->schema([
                        TextInput::make('project_number')
                            ->label(trans('projects.project_number'))
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50),

                        TextInput::make('name')
                            ->label(trans('projects.name'))
                            ->required()
                            ->maxLength(255),

                        Select::make('client_id')
                            ->label(trans('projects.client'))
                            ->relationship(
                                'client',
                                'name',
                                fn ($query) => $query->where('company_id', auth()->user()->company_id),
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->label(trans('clients.name'))
                                    ->required()
                                    ->maxLength(255),
                            ]),

                        Select::make('status')
                            ->label(trans('projects.status'))
                            ->options(ProjectStatus::class)
                            ->required()
                            ->default(ProjectStatus::Active->value)
                            ->enum(ProjectStatus::class),

                        DateTimePicker::make('started_at')
                            ->label(trans('projects.started_at'))
                            ->nullable(),

                        DateTimePicker::make('ended_at')
                            ->label(trans('projects.ended_at'))
                            ->nullable()
                            ->after('started_at'),

                        TextInput::make('budget')
                            ->label(trans('projects.budget'))
                            ->numeric()
                            ->prefix('$')
                            ->nullable(),

                        TextInput::make('estimated_hours')
                            ->label(trans('projects.estimated_hours'))
                            ->numeric()
                            ->suffix(trans('projects.hours'))
                            ->nullable(),
                    ])
                    ->columns(2),
            ]);
    }
}
