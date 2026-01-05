<?php

namespace App\Filament\Company\Resources\Timesheets;

use App\Filament\Company\Resources\Timesheets\Pages;
use App\Filament\Company\Resources\Timesheets\Schemas\TimesheetForm;
use App\Filament\Company\Resources\Timesheets\Tables\TimesheetsTable;
use App\Models\Timesheet;
use Filament\Resources\Resource;

class TimesheetResource extends Resource
{
    protected static ?string $model = Timesheet::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationGroup = 'Time Tracking';

    protected static ?int $navigationSort = 1;

    public static function form(\Filament\Forms\Form $form): \Filament\Forms\Form
    {
        return $form->schema(TimesheetForm::schema());
    }

    public static function table(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        return TimesheetsTable::table($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTimesheets::route('/'),
            'create' => Pages\CreateTimesheet::route('/create'),
            'edit' => Pages\EditTimesheet::route('/{record}/edit'),
        ];
    }

    public static function getLabel(): string
    {
        return trans('timesheets.singular');
    }

    public static function getPluralLabel(): string
    {
        return trans('timesheets.plural');
    }
}
