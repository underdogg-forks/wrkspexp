<?php

namespace Database\Seeders;

use App\Enums\RelationType;
use App\Models\Company;
use App\Models\Relation;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

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
        ]);

        // Add company communicatables
        $defaultCompany->communicatables()->create([
            'type' => 'email',
            'value' => 'admin@ivplv2.com',
            'is_primary' => true,
        ]);

        // Create admin role
        $adminRole = Role::firstOrCreate(['name' => 'admin']);

        // Create test user
        $user = User::create([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        // Attach user to default company with admin role
        $user->companies()->attach($defaultCompany->id, ['role_id' => $adminRole->id]);

        // Create a sample relation
        $sampleRelation = Relation::create([
            'name' => 'Sample Client',
            'notes' => 'This is a sample client for testing',
        ]);

        // Add relation to company as client
        $sampleRelation->companies()->attach($defaultCompany->id, [
            'relation_type' => RelationType::Client->value,
        ]);

        // Add communicatables to relation
        $sampleRelation->communicatables()->create([
            'type' => 'email',
            'value' => 'client@example.com',
            'is_primary' => true,
        ]);

        $sampleRelation->communicatables()->create([
            'type' => 'phone',
            'value' => '+1234567890',
            'is_primary' => true,
        ]);

        // Add address to relation
        $sampleRelation->addresses()->create([
            'address_line_1' => '123 Main Street',
            'city' => 'New York',
            'state' => 'NY',
            'postal_code' => '10001',
            'country' => 'USA',
            'is_primary' => true,
        ]);
    }
}

