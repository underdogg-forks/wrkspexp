<?php

namespace App\Filament\Company\Resources\Tasks;

use App\Filament\Company\Resources\Tasks\Pages\CreateTask;
use App\Filament\Company\Resources\Tasks\Pages\EditTask;
use App\Filament\Company\Resources\Tasks\Pages\ListTasks;
use App\Filament\Company\Resources\Tasks\Schemas\TaskForm;
use App\Filament\Company\Resources\Tasks\Tables\TasksTable;
use App\Models\Task;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::CheckCircle;

    protected static ?string $recordTitleAttribute = 'title';

    protected static string|UnitEnum|null $navigationGroup = 'Operations';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return TaskForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TasksTable::configure($table);
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
            'index' => ListTasks::route('/'),
            'create' => CreateTask::route('/create'),
            'edit' => EditTask::route('/{record}/edit'),
        ];
    }

    public static function getLabel(): string
    {
        return trans('tasks.task');
    }

    public static function getPluralLabel(): string
    {
        return trans('tasks.tasks');
    }
}
