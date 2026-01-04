<?php

namespace App\Filament\Workspace\Pages;

use Filament\Pages\Page;

class Workspace extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static string $view = 'filament.workspace.pages.workspace';

    protected static ?string $title = 'Select Company';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public function getCompanies()
    {
        return auth()->user()->companies;
    }
}
