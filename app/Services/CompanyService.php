<?php

namespace App\Services;

use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class CompanyService
{
    public function create(array $data): Company
    {
        // Generate company number if not provided
        if (empty($data['company_number'])) {
            $data['company_number'] = $this->generateCompanyNumber();
        }

        // Generate slug from name if not provided
        if (empty($data['slug']) && !empty($data['name'])) {
            $data['slug'] = Str::slug($data['name']) . '-' . now()->timestamp;
        }

        return Company::create($data);
    }

    public function update(Company $company, array $data): Company
    {
        $company->update($data);
        return $company->fresh();
    }

    public function delete(Company $company): bool
    {
        return $company->delete();
    }

    public function generateCompanyNumber(): string
    {
        $year = now()->year;
        $prefix = "COM-{$year}-";
        
        $lastCompany = Company::where('company_number', 'like', $prefix . '%')
            ->orderBy('company_number', 'desc')
            ->first();

        if ($lastCompany) {
            $lastNumber = (int) substr($lastCompany->company_number, strlen($prefix));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
    }

    public function addUser(Company $company, int $userId, ?int $roleId = null): void
    {
        if ($company->users()->where('user_id', $userId)->exists()) {
            return; // Early return: user already associated
        }

        $pivotData = [];
        if ($roleId) {
            $pivotData['role_id'] = $roleId;
        }

        $company->users()->attach($userId, $pivotData);
    }

    public function removeUser(Company $company, int $userId): void
    {
        $company->users()->detach($userId);
    }

    public function getUsers(Company $company): Collection
    {
        return $company->users;
    }

    public function duplicate(Company $company): Company
    {
        $newCompany = $company->replicate();
        $newCompany->company_number = $this->generateCompanyNumber();
        $newCompany->name = $company->name . ' (Copy)';
        $newCompany->slug = Str::slug($newCompany->name) . '-' . now()->timestamp;
        $newCompany->save();

        return $newCompany;
    }
}
