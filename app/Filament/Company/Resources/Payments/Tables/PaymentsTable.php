<?php

namespace App\Filament\Company\Resources\Payments\Tables;

use App\Enums\PaymentMethod;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('payment_number')
                    ->label(trans('payments.payment_number'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('invoice.invoice_number')
                    ->label(trans('payments.invoice_number'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('invoice.client.relation.name')
                    ->label('Client')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('amount')
                    ->label(trans('payments.amount'))
                    ->money('USD')
                    ->sortable(),

                BadgeColumn::make('payment_method')
                    ->label(trans('payments.payment_method'))
                    ->enum(PaymentMethod::class)
                    ->colors(fn (PaymentMethod $state): string => $state->color()),

                TextColumn::make('reference')
                    ->label(trans('payments.reference'))
                    ->searchable()
                    ->toggleable()
                    ->limit(30),

                TextColumn::make('paid_at')
                    ->label(trans('payments.paid_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('payment_method')
                    ->label(trans('payments.payment_method'))
                    ->options(PaymentMethod::class),

                SelectFilter::make('invoice_id')
                    ->label(trans('payments.invoice'))
                    ->relationship('invoice', 'invoice_number')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('payment_number', 'desc');
    }
}
