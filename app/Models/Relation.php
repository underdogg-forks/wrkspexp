<?php

namespace App\Models;

use App\Enums\RelationType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Relation extends Model
{
    public $timestamps = false;

    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'company_relation')
            ->withPivot('relation_type')
            ->using(CompanyRelation::class);
    }

    public function communicatables(): MorphMany
    {
        return $this->morphMany(Communicatable::class, 'communicatable');
    }

    public function addresses(): MorphMany
    {
        return $this->morphMany(Address::class, 'addressable');
    }

    public function primaryEmail()
    {
        return $this->communicatables()
            ->where('type', 'email')
            ->where('is_primary', true)
            ->first();
    }

    public function primaryPhone()
    {
        return $this->communicatables()
            ->where('type', 'phone')
            ->where('is_primary', true)
            ->first();
    }

    public function primaryAddress()
    {
        return $this->addresses()
            ->where('is_primary', true)
            ->first();
    }
}
