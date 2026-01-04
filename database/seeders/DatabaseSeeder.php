<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create default company
        $defaultCompany = Company::create([
            'name' => 'IVPLV2 Company',
            'slug' => 'ivplv2',
            'description' => 'Default company',
            'email' => 'admin@ivplv2.com',
        ]);

        // Create test user
        $user = User::create([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        // Attach user to default company
        $user->companies()->attach($defaultCompany->id, ['role' => 'admin']);
    }
}

