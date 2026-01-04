<?php

namespace App\Filament\Company\Resources\Projects;

use App\Filament\Company\Resources\Projects\Pages\CreateProject;
use App\Filament\Company\Resources\Projects\Pages\EditProject;
use App\Filament\Company\Resources\Projects\Pages\ListProjects;
use App\Filament\Company\Resources\Projects\Schemas\ProjectForm;
use App\Filament\Company\Resources\Projects\Tables\ProjectsTable;
use App\Models\Project;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::FolderOpen;

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|UnitEnum|null $navigationGroup = 'Operations';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return ProjectForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProjectsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProjects::route('/'),
            'create' => CreateProject::route('/create'),
            'edit' => EditProject::route('/{record}/edit'),
        ];
    }

    public static function getLabel(): string
    {
        return trans('projects.singular');
    }

    public static function getPluralLabel(): string
    {
        return trans('projects.plural');
    }
}
