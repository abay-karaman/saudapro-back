<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Truck extends Model
{
    protected $fillable = [
        'name',
        'code',
        'number',
        'capacity',
        'payload',
        'driver_id',
    ];

    public function ttn(): BelongsTo
    {
        return $this->belongsTo(Ttn::class);
    }

    public function driver(): belongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }
}
