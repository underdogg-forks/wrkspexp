<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Expense extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'incurred_at' => 'datetime',
            'amount' => 'decimal:2',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function items(): MorphMany
    {
        return $this->morphMany(Item::class, 'itemable');
    }
}
