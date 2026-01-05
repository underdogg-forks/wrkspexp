<?php

namespace App\Filament\Company\Resources\Companies\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;

class CompanyForm
{
    public static function schema(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(trans('companies.label'))
                    ->schema([
                        TextInput::make('company_number')
                            ->label(trans('companies.company_number'))
                            ->disabled()
                            ->dehydrated(false)
                            ->visible(fn ($record) => $record !== null),

                        TextInput::make('name')
                            ->label(trans('companies.name'))
                            ->required()
                            ->maxLength(255)
                            ->placeholder(trans('companies.name_placeholder')),

                        TextInput::make('slug')
                            ->label(trans('companies.slug'))
                            ->maxLength(255)
                            ->placeholder(trans('companies.slug_placeholder'))
                            ->helperText('Leave empty to auto-generate from name'),

                        FileUpload::make('logo')
                            ->label(trans('companies.logo'))
                            ->image()
                            ->maxSize(2048)
                            ->directory('logos'),

                        TextInput::make('website')
                            ->label(trans('companies.website'))
                            ->url()
                            ->maxLength(255)
                            ->placeholder(trans('companies.website_placeholder')),

                        TextInput::make('email')
                            ->label(trans('companies.email'))
                            ->email()
                            ->maxLength(255)
                            ->placeholder(trans('companies.email_placeholder')),

                        TextInput::make('phone')
                            ->label(trans('companies.phone'))
                            ->tel()
                            ->maxLength(50)
                            ->placeholder(trans('companies.phone_placeholder')),

                        TextInput::make('vat_number')
                            ->label(trans('companies.vat_number'))
                            ->maxLength(50),

                        TextInput::make('tax_id')
                            ->label(trans('companies.tax_id'))
                            ->maxLength(50),

                        TextInput::make('registration_number')
                            ->label(trans('companies.registration_number'))
                            ->maxLength(50),

                        Textarea::make('description')
                            ->label(trans('companies.description'))
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
