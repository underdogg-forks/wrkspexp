<?php

namespace App\Filament\Company\Resources\Quotes\Pages;

use App\Filament\Company\Resources\Quotes\QuoteResource;
use App\Services\QuoteService;
use Filament\Resources\Pages\CreateRecord;

class CreateQuote extends CreateRecord
{
    protected static string $resource = QuoteResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $service = app(QuoteService::class);
        $company = filament()->getTenant();
        $quote = $service->create($company, $data);
        
        $this->record = $quote;
        $this->halt();
        
        return [];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
