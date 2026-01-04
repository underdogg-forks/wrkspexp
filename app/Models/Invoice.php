<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Invoice extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'issued_at' => 'datetime',
            'due_at' => 'datetime',
            'status' => InvoiceStatus::class,
            'subtotal' => 'decimal:2',
            'tax' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function items(): MorphMany
    {
        return $this->morphMany(Item::class, 'itemable');
    }

    public function notesDescriptions(): MorphMany
    {
        return $this->morphMany(NoteDescription::class, 'notable');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
