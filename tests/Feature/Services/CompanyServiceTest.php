<?php

namespace Tests\Feature\Services;

use App\Models\Company;
use App\Models\User;
use App\Services\CompanyService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyServiceTest extends TestCase
{
    use RefreshDatabase;

    private CompanyService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CompanyService();
        Model::unguard();
    }

    /** @test */
    public function it_creates_company_with_generated_number(): void
    {
        // Arrange
        $data = [
            'name' => 'Acme Corporation',
        ];

        // Act
        $company = $this->service->create($data);

        // Assert
        $this->assertDatabaseHas('companies', [
            'name' => 'Acme Corporation',
        ]);
        $this->assertNotEmpty($company->company_number);
        $this->assertStringStartsWith('COM-' . now()->year . '-', $company->company_number);
        $this->assertNotEmpty($company->slug);
    }

    /** @test */
    public function it_creates_company_with_custom_number(): void
    {
        // Arrange
        $data = [
            'company_number' => 'CUSTOM-001',
            'name' => 'Custom Corp',
            'slug' => 'custom-corp',
        ];

        // Act
        $company = $this->service->create($data);

        // Assert
        $this->assertEquals('CUSTOM-001', $company->company_number);
        $this->assertEquals('Custom Corp', $company->name);
    }

    /** @test */
    public function it_generates_sequential_company_numbers(): void
    {
        // Arrange & Act
        $company1 = $this->service->create(['name' => 'Company 1']);
        $company2 = $this->service->create(['name' => 'Company 2']);
        $company3 = $this->service->create(['name' => 'Company 3']);

        // Assert
        $this->assertStringEndsWith('000001', $company1->company_number);
        $this->assertStringEndsWith('000002', $company2->company_number);
        $this->assertStringEndsWith('000003', $company3->company_number);
    }

    /** @test */
    public function it_updates_company(): void
    {
        // Arrange
        $company = Company::factory()->create(['name' => 'Old Name']);

        // Act
        $updated = $this->service->update($company, ['name' => 'New Name']);

        // Assert
        $this->assertEquals('New Name', $updated->name);
        $this->assertDatabaseHas('companies', [
            'id' => $company->id,
            'name' => 'New Name',
        ]);
    }

    /** @test */
    public function it_deletes_company(): void
    {
        // Arrange
        $company = Company::factory()->create();

        // Act
        $result = $this->service->delete($company);

        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseMissing('companies', ['id' => $company->id]);
    }

    /** @test */
    public function it_adds_user_to_company(): void
    {
        // Arrange
        $company = Company::factory()->create();
        $user = User::factory()->create();

        // Act
        $this->service->addUser($company, $user->id);

        // Assert
        $this->assertTrue($company->users()->where('user_id', $user->id)->exists());
    }

    /** @test */
    public function it_adds_user_to_company_with_role(): void
    {
        // Arrange
        $company = Company::factory()->create();
        $user = User::factory()->create();
        $roleId = 1;

        // Act
        $this->service->addUser($company, $user->id, $roleId);

        // Assert
        $pivot = $company->users()->where('user_id', $user->id)->first()->pivot;
        $this->assertEquals($roleId, $pivot->role_id);
    }

    /** @test */
    public function it_does_not_add_duplicate_user(): void
    {
        // Arrange
        $company = Company::factory()->create();
        $user = User::factory()->create();
        $this->service->addUser($company, $user->id);

        // Act
        $this->service->addUser($company, $user->id); // Try to add again

        // Assert
        $this->assertEquals(1, $company->users()->where('user_id', $user->id)->count());
    }

    /** @test */
    public function it_removes_user_from_company(): void
    {
        // Arrange
        $company = Company::factory()->create();
        $user = User::factory()->create();
        $this->service->addUser($company, $user->id);

        // Act
        $this->service->removeUser($company, $user->id);

        // Assert
        $this->assertFalse($company->users()->where('user_id', $user->id)->exists());
    }

    /** @test */
    public function it_gets_company_users(): void
    {
        // Arrange
        $company = Company::factory()->create();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $this->service->addUser($company, $user1->id);
        $this->service->addUser($company, $user2->id);

        // Act
        $users = $this->service->getUsers($company);

        // Assert
        $this->assertCount(2, $users);
        $this->assertTrue($users->contains($user1));
        $this->assertTrue($users->contains($user2));
    }

    /** @test */
    public function it_duplicates_company(): void
    {
        // Arrange
        $original = Company::factory()->create([
            'name' => 'Original Company',
            'company_number' => 'COM-2026-000001',
        ]);

        // Act
        $duplicate = $this->service->duplicate($original);

        // Assert
        $this->assertNotEquals($original->id, $duplicate->id);
        $this->assertNotEquals($original->company_number, $duplicate->company_number);
        $this->assertStringContains('(Copy)', $duplicate->name);
        $this->assertStringStartsWith('COM-' . now()->year . '-', $duplicate->company_number);
        $this->assertDatabaseHas('companies', [
            'id' => $duplicate->id,
            'name' => 'Original Company (Copy)',
        ]);
    }

    /** @test */
    public function it_generates_unique_slugs(): void
    {
        // Arrange & Act
        $company1 = $this->service->create(['name' => 'Test Company']);
        $company2 = $this->service->create(['name' => 'Test Company']);

        // Assert
        $this->assertNotEquals($company1->slug, $company2->slug);
    }

    /** @test */
    public function it_allows_custom_slug(): void
    {
        // Arrange
        $data = [
            'name' => 'My Company',
            'slug' => 'my-custom-slug',
        ];

        // Act
        $company = $this->service->create($data);

        // Assert
        $this->assertEquals('my-custom-slug', $company->slug);
    }

    /** @test */
    public function it_returns_fresh_instance_after_update(): void
    {
        // Arrange
        $company = Company::factory()->create(['name' => 'Original']);

        // Act
        $updated = $this->service->update($company, ['name' => 'Updated']);
        
        // Assert
        $this->assertEquals('Updated', $updated->name);
        $this->assertNotSame($company, $updated);
    }
}
