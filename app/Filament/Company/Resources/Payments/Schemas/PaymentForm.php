<?php

namespace App\Filament\Company\Resources\Payments\Schemas;

use App\Enums\PaymentMethod;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Section;
use Filament\Schemas\Schema;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(trans('payments.payment_details'))
                    ->schema([
                        TextInput::make('payment_number')
                            ->label(trans('payments.payment_number'))
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50),

                        Select::make('invoice_id')
                            ->label(trans('payments.invoice'))
                            ->relationship('invoice', 'invoice_number', fn ($query) => 
                                $query->whereHas('client', fn($q) => $q->where('company_id', filament()->getTenant()->id))
                            )
                            ->searchable()
                            ->preload()
                            ->required(),

                        TextInput::make('amount')
                            ->label(trans('payments.amount'))
                            ->numeric()
                            ->prefix('$')
                            ->required()
                            ->minValue(0.01),

                        Select::make('payment_method')
                            ->label(trans('payments.payment_method'))
                            ->options(PaymentMethod::class)
                            ->required()
                            ->enum(PaymentMethod::class),

                        DateTimePicker::make('paid_at')
                            ->label(trans('payments.paid_at'))
                            ->required()
                            ->default(now()),

                        TextInput::make('reference')
                            ->label(trans('payments.reference'))
                            ->maxLength(255)
                            ->nullable(),

                        Textarea::make('notes')
                            ->label(trans('payments.notes'))
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
