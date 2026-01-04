<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Company;
use App\Models\Relation;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition(): array
    {
        return [
            'relation_id' => Relation::factory(),
            'company_id' => Company::factory(),
        ];
    }
}
