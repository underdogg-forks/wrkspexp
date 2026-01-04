<?php

namespace App\Http\Responses;

use Filament\Facades\Filament;
use Filament\Http\Responses\Auth\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Http\RedirectResponse;

class CustomLoginResponse implements LoginResponseContract
{
    public function toResponse($request): RedirectResponse
    {
        $user = auth()->user();

        // Admin users → Workspace overview (all companies)
        if ($user->hasRole('admin')) {
            return redirect()->intended('/workspace');
        }

        // Get user's companies for employee and client redirects
        $companies = $user->companies;

        // No companies assigned → fallback to home
        if ($companies->isEmpty()) {
            return redirect()->intended(Filament::getUrl());
        }

        // Get default company (first company)
        $defaultCompany = $companies->first();

        // Employee or Client → Default company workspace
        if ($user->hasAnyRole(['employee', 'manager', 'client'])) {
            return redirect()->intended("/company/{$defaultCompany->slug}");
        }

        // Fallback for users without specific roles
        return redirect()->intended(Filament::getUrl());
    }
}
