<?php

namespace App\Models;

use App\Enums\QuoteStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Quote extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'issued_at' => 'datetime',
            'expires_at' => 'datetime',
            'status' => QuoteStatus::class,
            'subtotal' => 'decimal:2',
            'tax' => 'decimal:2',
            'total' => 'decimal:2',
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

    public function notesDescriptions(): MorphMany
    {
        return $this->morphMany(NoteDescription::class, 'notable');
    }

    public function prospect(): BelongsTo
    {
        return $this->belongsTo(Relation::class, 'client_id');
    }
}
