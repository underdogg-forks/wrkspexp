<?php

namespace App\Providers\Filament;

use App\Http\Responses\CustomLoginResponse;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class WorkspacePanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('workspace')
            ->path('workspace')
            ->login()
            ->loginRouteSlug('login')
            ->colors([
                'primary' => Color::Blue,
            ])
            ->discoverPages(in: app_path('Filament/Workspace/Pages'), for: 'App\Filament\Workspace\Pages')
            ->pages([
                \App\Filament\Workspace\Pages\Workspace::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }

    public function register(): void
    {
        parent::register();

        // Register custom login response for role-based redirects
        $this->app->singleton(LoginResponse::class, CustomLoginResponse::class);
    }
}
