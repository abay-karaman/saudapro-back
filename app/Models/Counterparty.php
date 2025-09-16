<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Counterparty extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'uid',
        'bin_iin',
        'phone',
        'rep_phones',
        'stores'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');    //для клиента

    }

    public function representatives(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'counterparty_representative', 'counterparty_id', 'representative_id');   //для торгового
    }

    public function stores(): HasMany
    {
        return $this->hasMany(Store::class, 'counterparty_id');
    }

    public function orders(): hasMany
    {
        return $this->hasMany(Order::class, 'counterparty_id');
    }
}
