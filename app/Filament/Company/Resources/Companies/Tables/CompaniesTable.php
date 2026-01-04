<?php

namespace App\Filament\Company\Resources\Companies\Tables;

use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CompaniesTable
{
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('logo')
                    ->label(trans('companies.logo'))
                    ->circular()
                    ->defaultImageUrl(url('/images/placeholder-company.png')),

                TextColumn::make('company_number')
                    ->label(trans('companies.company_number'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label(trans('companies.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('email')
                    ->label(trans('companies.email'))
                    ->searchable()
                    ->icon('heroicon-o-envelope')
                    ->copyable(),

                TextColumn::make('phone')
                    ->label(trans('companies.phone'))
                    ->searchable()
                    ->icon('heroicon-o-phone')
                    ->copyable(),

                TextColumn::make('users_count')
                    ->label(trans('companies.users'))
                    ->counts('users')
                    ->badge()
                    ->color('success'),

                TextColumn::make('clients_count')
                    ->label(trans('companies.clients'))
                    ->counts('clients')
                    ->badge()
                    ->color('info'),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
