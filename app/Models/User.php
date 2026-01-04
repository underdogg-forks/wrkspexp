<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use BezhanSalleh\FilamentShield\Traits\HasPanelShield;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, HasTenants
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;

    use HasPanelShield;
    use HasRoles;
    use Notifiable;

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class)
            ->withPivot('role_id');
    }

    public function getCurrentCompanyAttribute(): ?Company
    {
        return session('current_company') 
            ? $this->companies()->where('companies.id', session('current_company'))->first() 
            : null;
    }

    public function getTenants(Panel $panel): Collection
    {
        return $this->companies;
    }

    public function canAccessTenant($tenant): bool
    {
        // Early return for admin - can access any tenant
        if ($this->hasRole('admin')) {
            return true;
        }

        // Check if user belongs to this company
        if (!$this->companies->contains($tenant)) {
            return false;
        }

        // Manager and employee can access their companies
        if ($this->hasAnyRole(['manager', 'employee'])) {
            return true;
        }

        // Client role has limited access - check specific permissions
        if ($this->hasRole('client')) {
            return $this->companies->contains($tenant);
        }

        return false;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        // Early return for admin access
        if ($this->hasRole('admin')) {
            return true;
        }

        // Manager and employee can access admin and company panels
        if ($panel->getId() === 'admin' || $panel->getId() === 'company') {
            return $this->hasAnyRole(['admin', 'manager', 'employee']);
        }

        // Workspace panel accessible to all authenticated users
        if ($panel->getId() === 'workspace') {
            return true;
        }

        return false;
    }
}

