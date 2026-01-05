<?php

namespace App\Filament\Company\Resources\Companies\Pages;

use App\Filament\Company\Resources\Companies\CompanyResource;
use App\Filament\Company\Resources\Companies\Schemas\CompanyForm;
use App\Services\CompanyService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCompany extends EditRecord
{
    protected static string $resource = CompanyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\Action::make('duplicate')
                ->label(trans('companies.duplicate'))
                ->icon('heroicon-o-document-duplicate')
                ->action(function () {
                    $service = app(CompanyService::class);
                    $duplicate = $service->duplicate($this->record);
                    $this->redirect(static::getResource()::getUrl('edit', ['record' => $duplicate]));
                })
                ->color('gray'),
        ];
    }

    public function form(\Filament\Forms\Form $form): \Filament\Forms\Form
    {
        return CompanyForm::schema($form);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $service = app(CompanyService::class);
        $service->update($this->record, $data);
        
        // Return empty array to prevent Filament's default save
        // since service already saved
        $this->halt();
        
        return [];
    }
}
