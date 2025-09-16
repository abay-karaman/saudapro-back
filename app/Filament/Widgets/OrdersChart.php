<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class OrdersChart extends ChartWidget
{
    protected static ?string $heading = 'Заказы по месяцам';

    protected function getData(): array
    {
        $orders = Order::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('COUNT(*) as total')
        )
            ->whereYear('created_at', now()->year)
            ->groupBy('month')
            ->pluck('total', 'month'); // [1 => 2433, 2 => 3454, ...]

        // Заполним все 12 месяцев, даже если нет заказов
        $data = [];
        for ($i = 1; $i <= 12; $i++) {
            $data[] = $orders[$i] ?? 0;
        }
        return [
            'datasets' => [
                [
                    'label' => 'Количество заказов',
                    'data' => $data,
                    'fill' => 'start',
                ],
            ],
            'labels' => ['Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июнь', 'Июль', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
