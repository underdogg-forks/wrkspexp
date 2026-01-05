<?php

namespace Tests\Feature\Auth;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanySwitchingTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_switch_to_another_company_they_belong_to(): void
    {
        // Arrange
        $user = User::factory()->create();
        $company1 = Company::factory()->create(['name' => 'Company 1', 'slug' => 'company-1']);
        $company2 = Company::factory()->create(['name' => 'Company 2', 'slug' => 'company-2']);
        
        $user->companies()->attach([$company1->id, $company2->id]);

        $this->actingAs($user);

        // Act - Switch to company 2
        $response = $this->get("/company/{$company2->slug}");

        // Assert
        $response->assertStatus(200);
        $this->assertEquals($company2->id, session('current_company_id'));
    }

    public function test_user_cannot_switch_to_company_they_do_not_belong_to(): void
    {
        // Arrange
        $user = User::factory()->create();
        $userCompany = Company::factory()->create(['slug' => 'user-company']);
        $otherCompany = Company::factory()->create(['slug' => 'other-company']);
        
        $user->companies()->attach($userCompany->id);

        $this->actingAs($user);

        // Act - Attempt to switch to company user doesn't belong to
        $response = $this->get("/company/{$otherCompany->slug}");

        // Assert
        $response->assertStatus(403);
    }

    public function test_switching_companies_updates_tenant_context(): void
    {
        // Arrange
        $user = User::factory()->create();
        $company1 = Company::factory()->create(['slug' => 'company-1']);
        $company2 = Company::factory()->create(['slug' => 'company-2']);
        
        $user->companies()->attach([$company1->id, $company2->id]);

        $this->actingAs($user);

        // Act - Switch to company 1
        $this->get("/company/{$company1->slug}");
        $firstCompanyId = session('current_company_id');

        // Switch to company 2
        $this->get("/company/{$company2->slug}");
        $secondCompanyId = session('current_company_id');

        // Assert
        $this->assertEquals($company1->id, $firstCompanyId);
        $this->assertEquals($company2->id, $secondCompanyId);
        $this->assertNotEquals($firstCompanyId, $secondCompanyId);
    }

    public function test_user_with_single_company_can_access_their_company(): void
    {
        // Arrange
        $user = User::factory()->create();
        $company = Company::factory()->create(['slug' => 'my-company']);
        
        $user->companies()->attach($company->id);

        $this->actingAs($user);

        // Act
        $response = $this->get("/company/{$company->slug}");

        // Assert
        $response->assertStatus(200);
        $this->assertEquals($company->id, session('current_company_id'));
    }

    public function test_admin_can_switch_between_all_companies(): void
    {
        // Arrange
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        
        $company1 = Company::factory()->create(['slug' => 'company-1']);
        $company2 = Company::factory()->create(['slug' => 'company-2']);
        $company3 = Company::factory()->create(['slug' => 'company-3']);
        
        $admin->companies()->attach([$company1->id, $company2->id, $company3->id]);

        $this->actingAs($admin);

        // Act & Assert - Admin can switch to all companies
        $response1 = $this->get("/company/{$company1->slug}");
        $response1->assertStatus(200);

        $response2 = $this->get("/company/{$company2->slug}");
        $response2->assertStatus(200);

        $response3 = $this->get("/company/{$company3->slug}");
        $response3->assertStatus(200);
    }

    public function test_employee_can_switch_between_assigned_companies(): void
    {
        // Arrange
        $employee = User::factory()->create();
        $employee->assignRole('employee');
        
        $company1 = Company::factory()->create(['slug' => 'assigned-1']);
        $company2 = Company::factory()->create(['slug' => 'assigned-2']);
        $unassignedCompany = Company::factory()->create(['slug' => 'unassigned']);
        
        $employee->companies()->attach([$company1->id, $company2->id]);

        $this->actingAs($employee);

        // Act & Assert - Can access assigned companies
        $response1 = $this->get("/company/{$company1->slug}");
        $response1->assertStatus(200);

        $response2 = $this->get("/company/{$company2->slug}");
        $response2->assertStatus(200);

        // Cannot access unassigned company
        $response3 = $this->get("/company/{$unassignedCompany->slug}");
        $response3->assertStatus(403);
    }

    public function test_tenant_context_persists_across_requests(): void
    {
        // Arrange
        $user = User::factory()->create();
        $company = Company::factory()->create(['slug' => 'my-company']);
        
        $user->companies()->attach($company->id);

        $this->actingAs($user);

        // Act - Set tenant context
        $this->get("/company/{$company->slug}");
        $initialCompanyId = session('current_company_id');

        // Make another request without switching
        $this->get("/company/{$company->slug}/dashboard");
        $persistedCompanyId = session('current_company_id');

        // Assert
        $this->assertEquals($company->id, $initialCompanyId);
        $this->assertEquals($initialCompanyId, $persistedCompanyId);
    }

    public function test_unauthenticated_user_cannot_switch_companies(): void
    {
        // Arrange
        $company = Company::factory()->create(['slug' => 'company']);

        // Act
        $response = $this->get("/company/{$company->slug}");

        // Assert
        $response->assertRedirect('/workspace/login');
    }
}
