<?php

namespace App\Filament\Company\Resources\Quotes;

use App\Filament\Company\Resources\Quotes\Pages\CreateQuote;
use App\Filament\Company\Resources\Quotes\Pages\EditQuote;
use App\Filament\Company\Resources\Quotes\Pages\ListQuotes;
use App\Filament\Company\Resources\Quotes\Schemas\QuoteForm;
use App\Filament\Company\Resources\Quotes\Tables\QuotesTable;
use App\Models\Quote;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class QuoteResource extends Resource
{
    protected static ?string $model = Quote::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::DocumentText;

    protected static ?string $recordTitleAttribute = 'quote_number';

    protected static string|UnitEnum|null $navigationGroup = 'Sales';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return QuoteForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return QuotesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListQuotes::route('/'),
            'create' => CreateQuote::route('/create'),
            'edit' => EditQuote::route('/{record}/edit'),
        ];
    }

    public static function getLabel(): string
    {
        return trans('quotes.quote');
    }

    public static function getPluralLabel(): string
    {
        return trans('quotes.quotes');
    }
}
