<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TtnItem extends Model
{
    protected $fillable = [
        'name',
        'ttn_id',
        'order_id',
        'status'
    ];

    public function ttn(): BelongsTo
    {
        return $this->belongsTo(Ttn::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
