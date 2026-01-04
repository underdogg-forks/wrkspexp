<?php

namespace App\Filament\Company\Resources\Payments\Pages;

use App\Filament\Company\Resources\Payments\PaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPayments extends ListRecords
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label(trans('payments.create_payment'))
                ->modalWidth('5xl')
                ->modalHeading(trans('payments.create_payment'))
                ->using(function (array $data): \App\Models\Payment {
                    $service = app(\App\Services\PaymentService::class);
                    $company = filament()->getTenant();
                    return $service->create($company, $data);
                }),
        ];
    }
}
