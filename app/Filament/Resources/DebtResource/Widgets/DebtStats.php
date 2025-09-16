<?php

namespace App\Filament\Resources\DebtResource\Widgets;

use App\Models\OrderPayment;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DebtStats extends BaseWidget
{
    protected function getFilters(): ?array
    {
        $couriers = User::where('role_id', 3)->pluck('name', 'id')->toArray();
        // pluck(name, id) — ключи должны быть id, а значения — имя

        return [
            'all' => 'Все курьеры',
            'today' => 'Сегодня',
            'week' => 'За неделю',
            'month' => 'За месяц',
            ...$couriers, // теперь ключи id, значения имена
        ];
    }

    protected function getStats(): array
    {
        $query = OrderPayment::query()->where('debt_amount', '>', 0);
        $filter = $this->getFilters(); // ✅ используем getFilter()

        // фильтр по курьеру
        if ($filter && User::where('id', $filter)->exists()) {
            $query->where('courier_id', $filter);
        }

        // фильтр по дате
        if ($filter === 'today') {
            $query->whereDate('created_at', now());
        } elseif ($filter === 'week') {
            $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($filter === 'month') {
            $query->whereMonth('created_at', now()->month);
        }

        return [
            Stat::make(
                'Количество должников',
                $query->count()
            ),

            Stat::make(
                'Общая сумма долга',
                number_format($query->sum('debt_amount'), 0, '.', ' ')
            ),
        ];
    }
}
