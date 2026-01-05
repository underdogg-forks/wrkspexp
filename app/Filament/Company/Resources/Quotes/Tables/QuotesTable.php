<?php

namespace App\Filament\Company\Resources\Quotes\Tables;

use App\Enums\QuoteStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class QuotesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('quote_number')
                    ->label(trans('quotes.quote_number'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('client.relation.name')
                    ->label(trans('quotes.client'))
                    ->searchable()
                    ->sortable(),

                BadgeColumn::make('status')
                    ->label(trans('quotes.status'))
                    ->enum(QuoteStatus::class)
                    ->colors(fn (QuoteStatus $state): string => $state->color()),

                TextColumn::make('total')
                    ->label(trans('quotes.total'))
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('issued_at')
                    ->label(trans('quotes.issued_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('expires_at')
                    ->label(trans('quotes.expires_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(trans('quotes.status'))
                    ->options(QuoteStatus::class),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('send')
                    ->label(trans('quotes.send_quote'))
                    ->icon('heroicon-o-paper-airplane')
                    ->visible(fn ($record) => $record->status === QuoteStatus::Draft)
                    ->action(function ($record, $action) {
                        $service = app(\App\Services\QuoteService::class);
                        $service->send($record);
                        $action->success();
                    }),
                Action::make('convertToInvoice')
                    ->label(trans('quotes.convert_to_invoice'))
                    ->icon('heroicon-o-document-check')
                    ->visible(fn ($record) => $record->status === QuoteStatus::Accepted)
                    ->action(function ($record, $action) {
                        $service = app(\App\Services\QuoteService::class);
                        $invoice = $service->convertToInvoice($record);
                        $action->success();
                        return redirect()->route('filament.company.resources.invoices.edit', ['record' => $invoice]);
                    }),
                Action::make('duplicate')
                    ->label(trans('quotes.duplicate_quote'))
                    ->icon('heroicon-o-document-duplicate')
                    ->action(function ($record, $action) {
                        $service = app(\App\Services\QuoteService::class);
                        $newQuote = $service->duplicate($record);
                        $action->success();
                        return redirect()->route('filament.company.resources.quotes.edit', ['record' => $newQuote]);
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('quote_number', 'desc');
    }
}
