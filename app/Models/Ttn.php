<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Ttn extends Model
{
    protected $fillable = [
        'uid',
        'name',
        'code',
        'date',
        'courier_id',
        'truck_id',
        'status'
    ];

    public function items(): hasMany
    {
        return $this->hasMany(TtnItem::class);
    }

    public function orders()
    {
        return $this->hasManyThrough(Order::class, TtnItem::class, 'ttn_id', 'id', 'id', 'order_id');
    }

    public function truck(): belongsTo
    {
        return $this->belongsTo(Truck::class);
    }

    public function courier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'courier_id');
    }

    public function refreshStatus()
    {
        $orders = $this->orders()
            ->select('orders.status')
            ->pluck('orders.status')
            ->toArray();

        if (empty($orders)) {
            return;
        }

        // все завершены
        if (count(array_unique($orders)) === 1 && $orders[0] === 'delivered') {
            $this->update(['status' => 'completed']);
            return;
        }

        // есть хотя бы один "в пути"
        if (in_array('on_way', $orders)) {
            $this->update(['status' => 'in_progress']);
            return;
        }

        // можно добавить ещё: если все cancelled → cancelled
    }
}
