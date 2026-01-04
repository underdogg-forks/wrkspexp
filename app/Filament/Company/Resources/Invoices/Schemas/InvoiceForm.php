<?php

namespace App\Filament\Company\Resources\Invoices\Schemas;

use App\Enums\InvoiceStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Schemas\Schema;
use Filament\Forms\Get;
use Filament\Forms\Set;

class InvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(trans('invoices.invoice_details'))
                    ->schema([
                        TextInput::make('invoice_number')
                            ->label(trans('invoices.invoice_number'))
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50),

                        Select::make('client_id')
                            ->label(trans('invoices.client'))
                            ->relationship('client', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('status')
                            ->label(trans('invoices.status'))
                            ->options(InvoiceStatus::class)
                            ->required()
                            ->default(InvoiceStatus::Draft->value)
                            ->enum(InvoiceStatus::class),

                        DateTimePicker::make('issued_at')
                            ->label(trans('invoices.issued_at'))
                            ->nullable(),

                        DateTimePicker::make('due_at')
                            ->label(trans('invoices.due_at'))
                            ->nullable(),

                        TextInput::make('tax_rate')
                            ->label(trans('invoices.tax_rate'))
                            ->numeric()
                            ->suffix('%')
                            ->default(0)
                            ->minValue(0)
                            ->maxValue(100),

                        TextInput::make('subtotal')
                            ->label(trans('invoices.subtotal'))
                            ->numeric()
                            ->prefix('$')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('total')
                            ->label(trans('invoices.total'))
                            ->numeric()
                            ->prefix('$')
                            ->required(),
                    ])
                    ->columns(2),

                Section::make(trans('invoices.items'))
                    ->schema([
                        Repeater::make('items')
                            ->label(trans('invoices.line_items'))
                            ->relationship()
                            ->schema([
                                TextInput::make('description')
                                    ->label(trans('invoices.description'))
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('quantity')
                                    ->label(trans('invoices.quantity'))
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(1)
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Set $set, Get $get) => 
                                        self::updateLineTotal($set, $get)
                                    ),

                                TextInput::make('unit_price')
                                    ->label(trans('invoices.unit_price'))
                                    ->numeric()
                                    ->prefix('$')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Set $set, Get $get) => 
                                        self::updateLineTotal($set, $get)
                                    ),

                                TextInput::make('line_total')
                                    ->label(trans('invoices.line_total'))
                                    ->numeric()
                                    ->prefix('$')
                                    ->disabled()
                                    ->dehydrated(false),
                            ])
                            ->columns(4)
                            ->defaultItems(1)
                            ->addActionLabel(trans('invoices.add_item'))
                            ->reorderableWithButtons()
                            ->collapsible(),
                    ]),
            ]);
    }

    protected static function updateLineTotal(Set $set, Get $get): void
    {
        $quantity = (float) $get('quantity');
        $unitPrice = (float) $get('unit_price');
        $lineTotal = $quantity * $unitPrice;
        
        $set('line_total', number_format($lineTotal, 2, '.', ''));
    }
}
