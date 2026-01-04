<?php

namespace App\Filament\Company\Resources\Payments;

use App\Filament\Company\Resources\Payments\Pages\CreatePayment;
use App\Filament\Company\Resources\Payments\Pages\EditPayment;
use App\Filament\Company\Resources\Payments\Pages\ListPayments;
use App\Filament\Company\Resources\Payments\Schemas\PaymentForm;
use App\Filament\Company\Resources\Payments\Tables\PaymentsTable;
use App\Models\Payment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Banknotes;

    protected static ?string $recordTitleAttribute = 'payment_number';

    protected static string|UnitEnum|null $navigationGroup = 'Finance';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return PaymentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PaymentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPayments::route('/'),
            'create' => CreatePayment::route('/create'),
            'edit' => EditPayment::route('/{record}/edit'),
        ];
    }

    public static function getLabel(): string
    {
        return trans('payments.payment');
    }

    public static function getPluralLabel(): string
    {
        return trans('payments.payments');
    }
}
