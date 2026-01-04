<?php

namespace App\Filament\Company\Resources\Payments\Pages;

use App\Filament\Company\Resources\Payments\PaymentResource;
use App\Services\PaymentService;
use Filament\Resources\Pages\CreateRecord;

class CreatePayment extends CreateRecord
{
    protected static string $resource = PaymentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $service = app(PaymentService::class);
        $company = filament()->getTenant();
        $payment = $service->create($company, $data);
        
        $this->record = $payment;
        $this->halt();
        
        return [];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
