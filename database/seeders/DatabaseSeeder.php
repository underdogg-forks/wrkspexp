<?php

namespace Database\Seeders;

use App\Enums\RelationType;
use App\Models\Client;
use App\Models\Company;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Project;
use App\Models\Quote;
use App\Models\Relation;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create permissions early
        $this->createPermissions();
        
        // Create roles with permissions
        $adminRole = $this->createRoles();
        
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

        $defaultCompany->communicatables()->create([
            'type' => 'phone',
            'value' => '+1-555-0100',
            'is_primary' => true,
        ]);

        // Add company address
        $defaultCompany->addresses()->create([
            'address_line_1' => '100 Business Plaza',
            'address_line_2' => 'Suite 500',
            'city' => 'New York',
            'state' => 'NY',
            'postal_code' => '10001',
            'country' => 'USA',
            'is_primary' => true,
        ]);

        // Create test admin user
        $adminUser = User::create([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $adminUser->companies()->attach($defaultCompany->id, ['role_id' => $adminRole->id]);

        // Create 10+ users per company with different roles
        $this->createUsers($defaultCompany, $adminRole);

        // Create products (both services and physical)
        $products = $this->createProducts($defaultCompany);

        // Create 10+ relations (clients) with realistic data
        $relations = $this->createRelations($defaultCompany);

        // For each relation, create extensive data
        foreach ($relations as $relation) {
            // Get the client pivot
            $client = Client::where('relation_id', $relation->id)
                ->where('company_id', $defaultCompany->id)
                ->first();

            if (!$client) {
                continue;
            }

            // Create 10+ invoices per client
            $this->createInvoicesForClient($client, $products);

            // Create 10+ quotes per client
            $this->createQuotesForClient($client, $products);

            // Create 5+ projects per client
            $this->createProjectsForClient($client);

            // Create 15+ tasks per client
            $this->createTasksForClient($client);

            // Create 10+ expenses per client
            $this->createExpensesForClient($client);
        }
    }

    private function createPermissions(): void
    {
        $resources = [
            'user', 'company', 'relation', 'client', 'invoice', 'quote', 
            'expense', 'project', 'task', 'product', 'item', 'role', 'permission'
        ];

        $actions = ['view', 'view_any', 'create', 'update', 'delete', 'restore', 'force_delete'];
        $specialActions = ['duplicate', 'import', 'export'];

        foreach ($resources as $resource) {
            foreach ($actions as $action) {
                Permission::firstOrCreate([
                    'name' => "{$action}_{$resource}",
                    'guard_name' => 'web',
                ]);
            }

            foreach ($specialActions as $action) {
                Permission::firstOrCreate([
                    'name' => "{$action}_{$resource}",
                    'guard_name' => 'web',
                ]);
            }
        }

        // Create page-specific permissions
        $pages = ['dashboard', 'settings', 'reports', 'analytics'];
        foreach ($pages as $page) {
            Permission::firstOrCreate([
                'name' => "view_{$page}",
                'guard_name' => 'web',
            ]);
        }
    }

    private function createRoles(): Role
    {
        // Admin role with all permissions
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $adminRole->givePermissionTo(Permission::all());

        // Manager role with most permissions except delete/force_delete
        $managerRole = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $managerPermissions = Permission::where('name', 'not like', '%force_delete%')
            ->where('name', 'not like', '%delete%')
            ->get();
        $managerRole->givePermissionTo($managerPermissions);

        // Employee role with view/create/update
        $employeeRole = Role::firstOrCreate(['name' => 'employee', 'guard_name' => 'web']);
        $employeePermissions = Permission::whereIn('name', function($query) {
            $query->select('name')
                ->from('permissions')
                ->where('name', 'like', 'view%')
                ->orWhere('name', 'like', 'create%')
                ->orWhere('name', 'like', 'update%')
                ->orWhere('name', 'like', 'export%');
        })->get();
        $employeeRole->givePermissionTo($employeePermissions);

        // Client role with limited view permissions
        $clientRole = Role::firstOrCreate(['name' => 'client', 'guard_name' => 'web']);
        $clientPermissions = Permission::where('name', 'like', 'view%')->get();
        $clientRole->givePermissionTo($clientPermissions);

        return $adminRole;
    }

    private function createUsers(Company $company, Role $adminRole): void
    {
        $roles = Role::all();
        
        for ($i = 1; $i <= 12; $i++) {
            $user = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);

            $role = $roles->random();
            $user->companies()->attach($company->id, ['role_id' => $role->id]);
        }
    }

    private function createProducts(Company $company): \Illuminate\Support\Collection
    {
        $products = collect();
        
        // Create 10 services
        for ($i = 0; $i < 10; $i++) {
            $products->push(Product::factory()->service()->create([
                'company_id' => $company->id,
            ]));
        }

        // Create 10 physical products
        for ($i = 0; $i < 10; $i++) {
            $products->push(Product::factory()->physical()->create([
                'company_id' => $company->id,
            ]));
        }

        return $products;
    }

    private function createRelations(Company $company): \Illuminate\Support\Collection
    {
        $relations = collect();

        for ($i = 0; $i < 15; $i++) {
            $relation = Relation::factory()->create();

            // Add communicatables
            $relation->communicatables()->create([
                'type' => 'email',
                'value' => fake()->companyEmail(),
                'is_primary' => true,
            ]);

            $relation->communicatables()->create([
                'type' => 'phone',
                'value' => fake()->phoneNumber(),
                'is_primary' => true,
            ]);

            // Add secondary contacts
            for ($j = 0; $j < rand(1, 3); $j++) {
                $relation->communicatables()->create([
                    'type' => fake()->randomElement(['email', 'phone']),
                    'value' => fake()->randomElement([fake()->email(), fake()->phoneNumber()]),
                    'is_primary' => false,
                ]);
            }

            // Add addresses
            $relation->addresses()->create([
                'address_line_1' => fake()->streetAddress(),
                'address_line_2' => fake()->optional()->secondaryAddress(),
                'city' => fake()->city(),
                'state' => fake()->stateAbbr(),
                'postal_code' => fake()->postcode(),
                'country' => 'USA',
                'is_primary' => true,
            ]);

            // Attach to company as client
            $relation->companies()->attach($company->id, [
                'relation_type' => RelationType::Client->value,
            ]);

            // Create client record
            Client::create([
                'relation_id' => $relation->id,
                'company_id' => $company->id,
            ]);

            $relations->push($relation);
        }

        return $relations;
    }

    private function createInvoicesForClient(Client $client, $products): void
    {
        for ($i = 0; $i < rand(12, 20); $i++) {
            $invoice = Invoice::factory()->create([
                'client_id' => $client->id,
            ]);

            // Add 3-8 items to each invoice
            for ($j = 0; $j < rand(3, 8); $j++) {
                $product = $products->random();
                $quantity = rand(1, 5);
                $unitPrice = $product->price;
                
                $invoice->items()->create([
                    'description' => $product->name,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total' => $quantity * $unitPrice,
                    'product_id' => $product->id,
                ]);
            }
        }
    }

    private function createQuotesForClient(Client $client, $products): void
    {
        for ($i = 0; $i < rand(10, 15); $i++) {
            $quote = Quote::factory()->create([
                'client_id' => $client->id,
            ]);

            // Add 2-6 items to each quote
            for ($j = 0; $j < rand(2, 6); $j++) {
                $product = $products->random();
                $quantity = rand(1, 10);
                $unitPrice = $product->price;
                
                $quote->items()->create([
                    'description' => $product->name,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total' => $quantity * $unitPrice,
                    'product_id' => $product->id,
                ]);
            }
        }
    }

    private function createProjectsForClient(Client $client): void
    {
        for ($i = 0; $i < rand(5, 8); $i++) {
            $project = Project::factory()->create([
                'client_id' => $client->id,
            ]);

            // Create 5-10 tasks per project
            for ($j = 0; $j < rand(5, 10); $j++) {
                Task::factory()->create([
                    'client_id' => $client->id,
                    'project_id' => $project->id,
                ]);
            }
        }
    }

    private function createTasksForClient(Client $client): void
    {
        // Create standalone tasks (not attached to projects)
        for ($i = 0; $i < rand(15, 25); $i++) {
            Task::factory()->create([
                'client_id' => $client->id,
                'project_id' => null,
            ]);
        }
    }

    private function createExpensesForClient(Client $client): void
    {
        for ($i = 0; $i < rand(10, 20); $i++) {
            Expense::factory()->create([
                'client_id' => $client->id,
            ]);
        }
    }
}

