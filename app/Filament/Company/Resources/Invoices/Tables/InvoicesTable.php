<?php

namespace App\Filament\Company\Resources\Invoices\Tables;

use App\Enums\InvoiceStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class InvoicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice_number')
                    ->label(trans('invoices.invoice_number'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('client.relation.name')
                    ->label(trans('invoices.client'))
                    ->searchable()
                    ->sortable(),

                BadgeColumn::make('status')
                    ->label(trans('invoices.status'))
                    ->enum(InvoiceStatus::class)
                    ->colors(fn (InvoiceStatus $state): string => $state->color()),

                TextColumn::make('total')
                    ->label(trans('invoices.total'))
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('issued_at')
                    ->label(trans('invoices.issued_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('due_at')
                    ->label(trans('invoices.due_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(trans('invoices.status'))
                    ->options(InvoiceStatus::class),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('duplicate')
                    ->label(trans('invoices.duplicate'))
                    ->icon('heroicon-o-document-duplicate')
                    ->action(function ($record, $action) {
                        $service = app(\App\Services\InvoiceService::class);
                        $newInvoice = $service->duplicate($record);
                        $action->success();
                        return redirect()->route('filament.company.resources.invoices.edit', ['record' => $newInvoice]);
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('invoice_number', 'desc');
    }
}
