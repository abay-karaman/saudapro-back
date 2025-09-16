<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uid',
        'representative_id',
        'counterparty_id',
        'store_id',
        'total_price',
        'total_collected',
        'total_delivered',
        'status',
        'comment',
        'payment_method',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id', 'id');
    }

    public function counterparty(): BelongsTo
    {
        return $this->belongsTo(Counterparty::class, 'counterparty_id', 'id');
    }

    public function representative(): BelongsTo
    {
        return $this->belongsTo(User::class, 'representative_id', 'id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function ttnItem()
    {
        return $this->hasOne(TtnItem::class);
    }

    public function ttn()
    {
        return $this->hasOneThrough(Ttn::class, TtnItem::class, 'order_id', 'id', 'id', 'ttn_id');
    }

    public function limitedItems()
    {
        return $this->hasMany(OrderItem::class)
            ->latest()
            ->take(5);
    }

    protected static function booted(): void
    {
        static::updated(function ($order) {
            if ($order->isDirty('status')) {
                $order->ttn?->refreshStatus();
            }
        });
    }

    protected static function boot(): void
    {
        parent::boot();

        // Генерация UUID
        static::creating(function ($order) {
            if (!$order->uid) {
                $order->uid = (string) Str::uuid(); // уникальный UUID v4
            }
        });
        // Обновление статуса ТТН при изменении статуса заказа
        static::updated(function ($order) {
            if ($order->isDirty('status')) {
                $order->ttn?->refreshStatus();
            }
        });
    }

    public function payments(): HasMany
    {
        return $this->hasMany(OrderPayment::class);
    }
}
