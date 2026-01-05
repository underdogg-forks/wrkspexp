<?php

namespace App\Filament\Company\Resources\Expenses\Tables;

use App\Enums\ExpenseStatus;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ExpensesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('expense_number')
                    ->label(trans('expenses.expense_number'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('title')
                    ->label(trans('expenses.title'))
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                TextColumn::make('vendor.name')
                    ->label(trans('expenses.vendor'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('expenseCategory.name')
                    ->label(trans('expenses.category'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('amount')
                    ->label(trans('expenses.amount'))
                    ->money('USD')
                    ->sortable(),

                BadgeColumn::make('status')
                    ->label(trans('expenses.status'))
                    ->enum(ExpenseStatus::class)
                    ->color(fn (ExpenseStatus $state): string => $state->color()),

                TextColumn::make('incurred_at')
                    ->label(trans('expenses.incurred_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(trans('expenses.status'))
                    ->options(ExpenseStatus::class),

                SelectFilter::make('vendor_id')
                    ->label(trans('expenses.vendor'))
                    ->relationship('vendor', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('expense_category_id')
                    ->label(trans('expenses.category'))
                    ->relationship('expenseCategory', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('submit')
                    ->label(trans('expenses.submit_for_approval'))
                    ->icon('heroicon-o-paper-airplane')
                    ->visible(fn ($record) => $record->status === ExpenseStatus::Draft)
                    ->action(function ($record, $action) {
                        $service = app(\App\Services\ExpenseService::class);
                        $service->submit($record);
                        $action->success();
                    }),
                Action::make('approve')
                    ->label(trans('expenses.approve'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === ExpenseStatus::Pending)
                    ->action(function ($record, $action) {
                        $service = app(\App\Services\ExpenseService::class);
                        $service->approve($record);
                        $action->success();
                    }),
                Action::make('reject')
                    ->label(trans('expenses.reject'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => $record->status === ExpenseStatus::Pending)
                    ->action(function ($record, $action) {
                        $service = app(\App\Services\ExpenseService::class);
                        $service->reject($record);
                        $action->success();
                    }),
                Action::make('mark_paid')
                    ->label(trans('expenses.mark_as_paid'))
                    ->icon('heroicon-o-banknotes')
                    ->visible(fn ($record) => $record->status === ExpenseStatus::Approved)
                    ->action(function ($record, $action) {
                        $service = app(\App\Services\ExpenseService::class);
                        $service->markAsPaid($record);
                        $action->success();
                    }),
                Action::make('duplicate')
                    ->label(trans('expenses.duplicate_expense'))
                    ->icon('heroicon-o-document-duplicate')
                    ->action(function ($record, $action) {
                        $service = app(\App\Services\ExpenseService::class);
                        $newExpense = $service->duplicate($record);
                        $action->success();
                        return redirect()->route('filament.company.resources.expenses.edit', ['record' => $newExpense]);
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('expense_number', 'desc');
    }
}
