<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Address extends Model
{
    public $timestamps = false;

    public function addressable(): MorphTo
    {
        return $this->morphTo();
    }

    protected function fullAddress(): Attribute
    {
        return Attribute::make(
            get: fn() => implode(', ', array_filter([
                $this->address_line_1,
                $this->address_line_2,
                $this->city,
                $this->state,
                $this->postal_code,
                $this->country,
            ]))
        );
    }
}
