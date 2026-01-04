<?php

namespace App\Filament\Workspace\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Collection;

class Workspace extends Page
{
    protected static ?string $title = null;

    public static function getTitle(): string
    {
        return trans('workspace.select_company');
    }

    protected string $view = 'filament.workspace.pages.workspace';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public function getCompanies(): Collection
    {
        return auth()->user()->companies()
            ->with(['communicatables' => function ($query) {
                $query->where('type', 'email')->where('is_primary', true);
            }])
            ->get();
    }
}
