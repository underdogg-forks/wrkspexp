<?php

namespace App\Filament\Company\Resources\Quotes\Pages;

use App\Filament\Company\Resources\Quotes\QuoteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListQuotes extends ListRecords
{
    protected static string $resource = QuoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label(trans('quotes.create_quote'))
                ->modalWidth('5xl')
                ->modalHeading(trans('quotes.create_quote'))
                ->using(function (array $data): \App\Models\Quote {
                    $service = app(\App\Services\QuoteService::class);
                    $company = filament()->getTenant();
                    return $service->create($company, $data);
                }),
        ];
    }
}
