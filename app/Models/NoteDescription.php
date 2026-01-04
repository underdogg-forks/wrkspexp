<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class NoteDescription extends Model
{
    public $timestamps = true;

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function notable(): MorphTo
    {
        return $this->morphTo();
    }

    public function isNote(): bool
    {
        return $this->type === 'note';
    }

    public function isDescription(): bool
    {
        return $this->type === 'description';
    }
}
