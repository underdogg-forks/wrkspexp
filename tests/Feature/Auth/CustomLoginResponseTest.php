<?php

namespace Tests\Feature\Auth;

use App\Http\Responses\CustomLoginResponse;
use App\Models\Company;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CustomLoginResponseTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'employee']);
        Role::create(['name' => 'manager']);
        Role::create(['name' => 'client']);
    }

    /** @test */
    public function it_redirects_admin_to_workspace(): void
    {
        // Arrange
        $user = User::factory()->create();
        $user->assignRole('admin');
        $this->actingAs($user);

        $response = new CustomLoginResponse();
        $request = Request::create('/login', 'POST');

        // Act
        $result = $response->toResponse($request);

        // Assert
        $this->assertEquals('/workspace', $result->getTargetUrl());
    }

    /** @test */
    public function it_redirects_employee_to_default_company(): void
    {
        // Arrange
        $user = User::factory()->create();
        $user->assignRole('employee');
        
        $company = Company::factory()->create(['slug' => 'test-company']);
        $user->companies()->attach($company);
        
        $this->actingAs($user);

        $response = new CustomLoginResponse();
        $request = Request::create('/login', 'POST');

        // Act
        $result = $response->toResponse($request);

        // Assert
        $this->assertEquals('/company/test-company', $result->getTargetUrl());
    }

    /** @test */
    public function it_redirects_manager_to_default_company(): void
    {
        // Arrange
        $user = User::factory()->create();
        $user->assignRole('manager');
        
        $company = Company::factory()->create(['slug' => 'manager-company']);
        $user->companies()->attach($company);
        
        $this->actingAs($user);

        $response = new CustomLoginResponse();
        $request = Request::create('/login', 'POST');

        // Act
        $result = $response->toResponse($request);

        // Assert
        $this->assertEquals('/company/manager-company', $result->getTargetUrl());
    }

    /** @test */
    public function it_redirects_client_to_company_portal(): void
    {
        // Arrange
        $user = User::factory()->create();
        $user->assignRole('client');
        
        $company = Company::factory()->create(['slug' => 'client-company']);
        $user->companies()->attach($company);
        
        $this->actingAs($user);

        $response = new CustomLoginResponse();
        $request = Request::create('/login', 'POST');

        // Act
        $result = $response->toResponse($request);

        // Assert
        $this->assertEquals('/company/client-company', $result->getTargetUrl());
    }

    /** @test */
    public function it_redirects_to_home_when_user_has_no_companies(): void
    {
        // Arrange
        $user = User::factory()->create();
        $user->assignRole('employee');
        // No companies attached
        
        $this->actingAs($user);

        $response = new CustomLoginResponse();
        $request = Request::create('/login', 'POST');

        // Act
        $result = $response->toResponse($request);

        // Assert
        $this->assertEquals(Filament::getUrl(), $result->getTargetUrl());
    }

    /** @test */
    public function it_selects_first_company_when_user_has_multiple_companies(): void
    {
        // Arrange
        $user = User::factory()->create();
        $user->assignRole('employee');
        
        $company1 = Company::factory()->create(['slug' => 'first-company']);
        $company2 = Company::factory()->create(['slug' => 'second-company']);
        
        $user->companies()->attach($company1);
        $user->companies()->attach($company2);
        
        $this->actingAs($user);

        $response = new CustomLoginResponse();
        $request = Request::create('/login', 'POST');

        // Act
        $result = $response->toResponse($request);

        // Assert
        $this->assertEquals('/company/first-company', $result->getTargetUrl());
    }

    /** @test */
    public function it_handles_user_without_roles(): void
    {
        // Arrange
        $user = User::factory()->create();
        // No roles assigned
        
        $company = Company::factory()->create();
        $user->companies()->attach($company);
        
        $this->actingAs($user);

        $response = new CustomLoginResponse();
        $request = Request::create('/login', 'POST');

        // Act
        $result = $response->toResponse($request);

        // Assert
        $this->assertEquals(Filament::getUrl(), $result->getTargetUrl());
    }

    /** @test */
    public function it_prioritizes_admin_role_over_other_roles(): void
    {
        // Arrange
        $user = User::factory()->create();
        $user->assignRole(['admin', 'employee']); // Multiple roles
        
        $company = Company::factory()->create();
        $user->companies()->attach($company);
        
        $this->actingAs($user);

        $response = new CustomLoginResponse();
        $request = Request::create('/login', 'POST');

        // Act
        $result = $response->toResponse($request);

        // Assert
        $this->assertEquals('/workspace', $result->getTargetUrl());
    }

    /** @test */
    public function it_resolves_company_slug_correctly(): void
    {
        // Arrange
        $user = User::factory()->create();
        $user->assignRole('employee');
        
        $company = Company::factory()->create([
            'name' => 'Test Company Ltd',
            'slug' => 'test-company-ltd'
        ]);
        $user->companies()->attach($company);
        
        $this->actingAs($user);

        $response = new CustomLoginResponse();
        $request = Request::create('/login', 'POST');

        // Act
        $result = $response->toResponse($request);

        // Assert
        $this->assertStringContainsString('test-company-ltd', $result->getTargetUrl());
    }
}
