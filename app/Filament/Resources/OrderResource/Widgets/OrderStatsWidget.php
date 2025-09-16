<?php

namespace App\Filament\Resources\OrderResource\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OrderStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Новые', Order::where('status', 'new')->count()),
            Stat::make('Завершенные', Order::where('status', 'delivered')->count()),
            Stat::make('Отмененные', Order::where('status', 'cancelled')->count()),
        ];
    }
}
