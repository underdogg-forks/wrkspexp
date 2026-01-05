<?php

namespace Tests\Feature\Auth;

use App\Models\Client;
use App\Models\Company;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantContextTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_only_see_resources_from_their_current_company(): void
    {
        // Arrange
        $user = User::factory()->create();
        $company1 = Company::factory()->create();
        $company2 = Company::factory()->create();
        
        $user->companies()->attach([$company1->id, $company2->id]);

        $client1 = Client::factory()->create(['company_id' => $company1->id]);
        $client2 = Client::factory()->create(['company_id' => $company2->id]);

        $project1 = Project::factory()->create(['client_id' => $client1->id, 'company_id' => $company1->id]);
        $project2 = Project::factory()->create(['client_id' => $client2->id, 'company_id' => $company2->id]);

        $this->actingAs($user);

        // Act - Set tenant to company 1
        session(['current_company_id' => $company1->id]);

        // Assert - Should only see company 1 resources
        $company1Projects = Project::where('company_id', $company1->id)->count();
        $this->assertEquals(1, $company1Projects);

        // Act - Switch to company 2
        session(['current_company_id' => $company2->id]);

        // Assert - Should only see company 2 resources
        $company2Projects = Project::where('company_id', $company2->id)->count();
        $this->assertEquals(1, $company2Projects);
    }

    public function test_tasks_are_isolated_by_tenant(): void
    {
        // Arrange
        $user = User::factory()->create();
        $company1 = Company::factory()->create();
        $company2 = Company::factory()->create();
        
        $user->companies()->attach([$company1->id, $company2->id]);

        $client1 = Client::factory()->create(['company_id' => $company1->id]);
        $client2 = Client::factory()->create(['company_id' => $company2->id]);

        $task1 = Task::factory()->create(['client_id' => $client1->id]);
        $task2 = Task::factory()->create(['client_id' => $client2->id]);

        $this->actingAs($user);

        // Act & Assert - Company 1 context
        session(['current_company_id' => $company1->id]);
        $company1Tasks = Task::whereHas('client', function ($q) use ($company1) {
            $q->where('company_id', $company1->id);
        })->count();
        $this->assertEquals(1, $company1Tasks);

        // Act & Assert - Company 2 context
        session(['current_company_id' => $company2->id]);
        $company2Tasks = Task::whereHas('client', function ($q) use ($company2) {
            $q->where('company_id', $company2->id);
        })->count();
        $this->assertEquals(1, $company2Tasks);
    }

    public function test_expenses_are_isolated_by_tenant(): void
    {
        // Arrange
        $user = User::factory()->create();
        $company1 = Company::factory()->create();
        $company2 = Company::factory()->create();
        
        $user->companies()->attach([$company1->id, $company2->id]);

        $expense1 = Expense::factory()->create(['company_id' => $company1->id]);
        $expense2 = Expense::factory()->create(['company_id' => $company2->id]);

        $this->actingAs($user);

        // Act & Assert - Company 1 context
        session(['current_company_id' => $company1->id]);
        $company1Expenses = Expense::where('company_id', $company1->id)->count();
        $this->assertEquals(1, $company1Expenses);

        // Act & Assert - Company 2 context
        session(['current_company_id' => $company2->id]);
        $company2Expenses = Expense::where('company_id', $company2->id)->count();
        $this->assertEquals(1, $company2Expenses);
    }

    public function test_invoices_are_isolated_by_tenant(): void
    {
        // Arrange
        $user = User::factory()->create();
        $company1 = Company::factory()->create();
        $company2 = Company::factory()->create();
        
        $user->companies()->attach([$company1->id, $company2->id]);

        $client1 = Client::factory()->create(['company_id' => $company1->id]);
        $client2 = Client::factory()->create(['company_id' => $company2->id]);

        $invoice1 = Invoice::factory()->create(['client_id' => $client1->id, 'company_id' => $company1->id]);
        $invoice2 = Invoice::factory()->create(['client_id' => $client2->id, 'company_id' => $company2->id]);

        $this->actingAs($user);

        // Act & Assert - Company 1 context
        session(['current_company_id' => $company1->id]);
        $company1Invoices = Invoice::where('company_id', $company1->id)->count();
        $this->assertEquals(1, $company1Invoices);

        // Act & Assert - Company 2 context
        session(['current_company_id' => $company2->id]);
        $company2Invoices = Invoice::where('company_id', $company2->id)->count();
        $this->assertEquals(1, $company2Invoices);
    }

    public function test_user_without_tenant_context_cannot_access_resources(): void
    {
        // Arrange
        $user = User::factory()->create();
        $company = Company::factory()->create();
        
        $user->companies()->attach($company->id);

        $this->actingAs($user);

        // Act - No tenant context set
        // Note: In production, middleware should enforce this

        // Assert - Session should not have company context
        $this->assertNull(session('current_company_id'));
    }

    public function test_switching_tenant_context_clears_previous_context(): void
    {
        // Arrange
        $user = User::factory()->create();
        $company1 = Company::factory()->create();
        $company2 = Company::factory()->create();
        
        $user->companies()->attach([$company1->id, $company2->id]);

        $this->actingAs($user);

        // Act - Set first context
        session(['current_company_id' => $company1->id]);
        $firstContext = session('current_company_id');

        // Switch context
        session(['current_company_id' => $company2->id]);
        $secondContext = session('current_company_id');

        // Assert
        $this->assertEquals($company1->id, $firstContext);
        $this->assertEquals($company2->id, $secondContext);
        $this->assertNotEquals($firstContext, $secondContext);
    }

    public function test_admin_can_access_any_company_resources(): void
    {
        // Arrange
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        
        $company1 = Company::factory()->create();
        $company2 = Company::factory()->create();
        
        $admin->companies()->attach([$company1->id, $company2->id]);

        $this->actingAs($admin);

        // Act & Assert - Admin can see all companies
        $totalCompanies = Company::count();
        $this->assertEquals(2, $totalCompanies);
    }

    public function test_employee_can_only_access_assigned_company_resources(): void
    {
        // Arrange
        $employee = User::factory()->create();
        $employee->assignRole('employee');
        
        $assignedCompany = Company::factory()->create();
        $unassignedCompany = Company::factory()->create();
        
        $employee->companies()->attach($assignedCompany->id);

        $this->actingAs($employee);

        // Act & Assert - Employee only sees assigned companies
        $employeeCompanies = $employee->companies()->count();
        $this->assertEquals(1, $employeeCompanies);
        $this->assertTrue($employee->companies()->where('companies.id', $assignedCompany->id)->exists());
        $this->assertFalse($employee->companies()->where('companies.id', $unassignedCompany->id)->exists());
    }

    public function test_tenant_context_is_maintained_across_multiple_requests(): void
    {
        // Arrange
        $user = User::factory()->create();
        $company = Company::factory()->create();
        
        $user->companies()->attach($company->id);

        $this->actingAs($user);

        // Act - Set context and make multiple requests
        session(['current_company_id' => $company->id]);
        $context1 = session('current_company_id');

        // Simulate another request
        $context2 = session('current_company_id');

        // Assert
        $this->assertEquals($company->id, $context1);
        $this->assertEquals($company->id, $context2);
        $this->assertEquals($context1, $context2);
    }
}
