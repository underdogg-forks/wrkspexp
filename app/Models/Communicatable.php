<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Communicatable extends Model
{
    public $timestamps = false;

    public function communicatable(): MorphTo
    {
        return $this->morphTo();
    }
}
