<?php

namespace App\Models;

use App\Enums\ExpenseStatus;
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
            'status' => ExpenseStatus::class,
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

    public function expenseCategory(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class);
    }

    public function items(): MorphMany
    {
        return $this->morphMany(Item::class, 'itemable');
    }

    public function notesDescriptions(): MorphMany
    {
        return $this->morphMany(NoteDescription::class, 'notable');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Relation::class, 'vendor_id');
    }
}
