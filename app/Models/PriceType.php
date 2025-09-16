<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PriceType extends Model
{
    protected $fillable = ['code', 'name'];

    public function prices(): HasMany
    {
        return $this->hasMany(Price::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
