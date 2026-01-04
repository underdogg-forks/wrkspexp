<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Company extends Model
{
    public $timestamps = false;

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role_id');
    }

    public function relations(): BelongsToMany
    {
        return $this->belongsToMany(Relation::class, 'company_relation')
            ->withPivot('relation_type')
            ->using(CompanyRelation::class);
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function communicatables(): MorphMany
    {
        return $this->morphMany(Communicatable::class, 'communicatable');
    }

    public function addresses(): MorphMany
    {
        return $this->morphMany(Address::class, 'addressable');
    }
}
