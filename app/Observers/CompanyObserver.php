<?php

namespace App\Observers;

use App\Models\Company;

class CompanyObserver
{
    public function creating(Company $company): void
    {
        // Add any pre-creation logic here
    }

    public function created(Company $company): void
    {
        // Add any post-creation logic here
    }

    public function updating(Company $company): void
    {
        // Add any pre-update logic here
    }

    public function updated(Company $company): void
    {
        // Add any post-update logic here
    }

    public function deleting(Company $company): void
    {
        // Add any pre-deletion logic here
    }

    public function deleted(Company $company): void
    {
        // Add any post-deletion logic here
    }
}
