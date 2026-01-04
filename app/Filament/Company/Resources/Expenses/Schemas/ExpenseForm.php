<?php

namespace App\Filament\Company\Resources\Expenses\Schemas;

use App\Enums\ExpenseStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Section;
use Filament\Schemas\Schema;

class ExpenseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(trans('expenses.expense_details'))
                    ->schema([
                        TextInput::make('expense_number')
                            ->label(trans('expenses.expense_number'))
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50),

                        TextInput::make('title')
                            ->label(trans('expenses.title'))
                            ->required()
                            ->maxLength(255),

                        Select::make('vendor_id')
                            ->label(trans('expenses.vendor'))
                            ->relationship('vendor', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('expense_category_id')
                            ->label(trans('expenses.category'))
                            ->relationship('expenseCategory', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        TextInput::make('amount')
                            ->label(trans('expenses.amount'))
                            ->numeric()
                            ->prefix('$')
                            ->required()
                            ->minValue(0),

                        Select::make('status')
                            ->label(trans('expenses.status'))
                            ->options(ExpenseStatus::class)
                            ->required()
                            ->default(ExpenseStatus::Draft->value)
                            ->enum(ExpenseStatus::class),

                        DateTimePicker::make('incurred_at')
                            ->label(trans('expenses.incurred_at'))
                            ->nullable(),

                        Textarea::make('description')
                            ->label(trans('expenses.description'))
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
