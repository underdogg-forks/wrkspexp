<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiTenancyTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_belong_to_multiple_companies(): void
    {
        $user = User::factory()->create();
        $company1 = Company::factory()->create(['slug' => 'company-1']);
        $company2 = Company::factory()->create(['slug' => 'company-2']);

        $user->companies()->attach($company1);
        $user->companies()->attach($company2);

        $this->assertCount(2, $user->companies);
        $this->assertTrue($user->companies->contains($company1));
        $this->assertTrue($user->companies->contains($company2));
    }

    public function test_user_can_access_tenant_they_belong_to(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create(['slug' => 'test-company']);
        $user->companies()->attach($company);

        $this->assertTrue($user->canAccessTenant($company));
    }

    public function test_user_cannot_access_tenant_they_dont_belong_to(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create(['slug' => 'test-company']);

        $this->assertFalse($user->canAccessTenant($company));
    }

    public function test_company_has_unique_slug(): void
    {
        Company::factory()->create(['slug' => 'unique-slug']);

        $this->expectException(\Illuminate\Database\QueryException::class);
        Company::factory()->create(['slug' => 'unique-slug']);
    }

    public function test_user_can_have_role_in_company(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $role = \Spatie\Permission\Models\Role::create(['name' => 'admin']);

        $user->companies()->attach($company, ['role_id' => $role->id]);

        $pivot = $user->companies()->wherePivot('company_id', $company->id)->first()->pivot;
        $this->assertEquals($role->id, $pivot->role_id);
    }
}
