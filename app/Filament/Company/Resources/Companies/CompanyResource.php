<?php

namespace App\Filament\Company\Resources\Companies;

use App\Filament\Company\Resources\Companies\Pages\CreateCompany;
use App\Filament\Company\Resources\Companies\Pages\EditCompany;
use App\Filament\Company\Resources\Companies\Pages\ListCompanies;
use App\Models\Company;
use Filament\Resources\Resource;

class CompanyResource extends Resource
{
    protected static ?string $model = Company::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 1;

    public static function getPages(): array
    {
        return [
            'index' => ListCompanies::route('/'),
            'create' => CreateCompany::route('/create'),
            'edit' => EditCompany::route('/{record}/edit'),
        ];
    }

    public static function getLabel(): string
    {
        return trans('companies.label');
    }

    public static function getPluralLabel(): string
    {
        return trans('companies.navigation_label');
    }
}
