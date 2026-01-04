<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Payment extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'paid_at' => 'datetime',
            'amount' => 'decimal:2',
            'payment_method' => PaymentMethod::class,
        ];
    }

    /**
     * Global scope to filter payments by current tenant/company
     */
    protected static function booted(): void
    {
        static::addGlobalScope('company', function (Builder $builder) {
            if (filament()->hasTenancy() && filament()->getTenant()) {
                $builder->whereHas('invoice.client', function ($query) {
                    $query->where('company_id', filament()->getTenant()->id);
                });
            }
        });
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
