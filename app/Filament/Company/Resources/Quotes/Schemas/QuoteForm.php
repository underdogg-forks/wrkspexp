<?php

namespace App\Filament\Company\Resources\Quotes\Schemas;

use App\Enums\QuoteStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Section;
use Filament\Schemas\Schema;

class QuoteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(trans('quotes.quote_details'))
                    ->schema([
                        TextInput::make('quote_number')
                            ->label(trans('quotes.quote_number'))
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50),

                        Select::make('client_id')
                            ->label(trans('quotes.client'))
                            ->relationship('client', 'name', fn ($query) => $query->where('company_id', filament()->getTenant()->id))
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('status')
                            ->label(trans('quotes.status'))
                            ->options(QuoteStatus::class)
                            ->required()
                            ->default(QuoteStatus::Draft->value)
                            ->enum(QuoteStatus::class),

                        DateTimePicker::make('issued_at')
                            ->label(trans('quotes.issued_at'))
                            ->nullable(),

                        DateTimePicker::make('expires_at')
                            ->label(trans('quotes.expires_at'))
                            ->nullable()
                            ->after('issued_at'),

                        TextInput::make('subtotal')
                            ->label(trans('quotes.subtotal'))
                            ->numeric()
                            ->prefix('$')
                            ->default(0),

                        TextInput::make('tax')
                            ->label(trans('quotes.tax'))
                            ->numeric()
                            ->prefix('$')
                            ->default(0),

                        TextInput::make('total')
                            ->label(trans('quotes.total'))
                            ->numeric()
                            ->prefix('$')
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }
}
