<?php

namespace App\Filament\Workspace\Pages;

use Filament\Pages\Page;

class Workspace extends Page
{
    protected static ?string $title = 'Select Company';

    protected string $view = 'filament.workspace.pages.workspace';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public function getCompanies()
    {
        return auth()->user()->companies;
    }
}
