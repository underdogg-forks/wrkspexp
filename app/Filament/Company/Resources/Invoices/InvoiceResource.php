<?php

namespace App\Filament\Company\Resources\Invoices;

use App\Filament\Company\Resources\Invoices\Pages\CreateInvoice;
use App\Filament\Company\Resources\Invoices\Pages\EditInvoice;
use App\Filament\Company\Resources\Invoices\Pages\ListInvoices;
use App\Filament\Company\Resources\Invoices\Schemas\InvoiceForm;
use App\Filament\Company\Resources\Invoices\Tables\InvoicesTable;
use App\Models\Invoice;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::DocumentText;

    protected static ?string $recordTitleAttribute = 'invoice_number';

    protected static string|UnitEnum|null $navigationGroup = 'Financial';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return InvoiceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InvoicesTable::configure($table);
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
            'index' => ListInvoices::route('/'),
            'create' => CreateInvoice::route('/create'),
            'edit' => EditInvoice::route('/{record}/edit'),
        ];
    }

    public static function getLabel(): string
    {
        return trans('invoices.singular');
    }

    public static function getPluralLabel(): string
    {
        return trans('invoices.plural');
    }
}
