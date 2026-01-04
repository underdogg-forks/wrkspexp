<?php

namespace Tests\Feature;

use App\Enums\RelationType;
use App\Models\Address;
use App\Models\Communicatable;
use App\Models\Company;
use App\Models\Relation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RelationTest extends TestCase
{
    use RefreshDatabase;

    public function test_relation_can_belong_to_multiple_companies(): void
    {
        $relation = Relation::factory()->create();
        $company1 = Company::factory()->create();
        $company2 = Company::factory()->create();

        $relation->companies()->attach($company1, ['relation_type' => RelationType::Client->value]);
        $relation->companies()->attach($company2, ['relation_type' => RelationType::Supplier->value]);

        $this->assertCount(2, $relation->companies);
    }

    public function test_relation_can_have_multiple_communicatables(): void
    {
        $relation = Relation::factory()->create();
        
        $relation->communicatables()->create([
            'type' => 'email',
            'value' => 'test@example.com',
            'is_primary' => true,
        ]);
        
        $relation->communicatables()->create([
            'type' => 'phone',
            'value' => '+1234567890',
            'is_primary' => true,
        ]);

        $this->assertCount(2, $relation->communicatables);
        $this->assertNotNull($relation->primaryEmail());
        $this->assertNotNull($relation->primaryPhone());
    }

    public function test_relation_can_have_multiple_addresses(): void
    {
        $relation = Relation::factory()->create();
        
        $relation->addresses()->create([
            'address_line_1' => '123 Main St',
            'city' => 'New York',
            'country' => 'USA',
            'is_primary' => true,
        ]);
        
        $relation->addresses()->create([
            'address_line_1' => '456 Oak Ave',
            'city' => 'Los Angeles',
            'country' => 'USA',
            'is_primary' => false,
        ]);

        $this->assertCount(2, $relation->addresses);
        $this->assertNotNull($relation->primaryAddress());
    }

    public function test_primary_email_returns_correct_record(): void
    {
        $relation = Relation::factory()->create();
        
        $relation->communicatables()->create([
            'type' => 'email',
            'value' => 'secondary@example.com',
            'is_primary' => false,
        ]);
        
        $primaryEmail = $relation->communicatables()->create([
            'type' => 'email',
            'value' => 'primary@example.com',
            'is_primary' => true,
        ]);

        $result = $relation->primaryEmail();
        $this->assertEquals('primary@example.com', $result->value);
    }

    public function test_address_full_address_attribute(): void
    {
        $address = new Address([
            'address_line_1' => '123 Main St',
            'address_line_2' => 'Apt 4B',
            'city' => 'New York',
            'state' => 'NY',
            'postal_code' => '10001',
            'country' => 'USA',
        ]);

        $expected = '123 Main St, Apt 4B, New York, NY, 10001, USA';
        $this->assertEquals($expected, $address->full_address);
    }
}
