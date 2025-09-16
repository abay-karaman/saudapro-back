<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class OrdersSumChart extends ChartWidget
{
    protected static ?string $heading = 'Сумма по месяцам';

    protected function getData(): array
    {
        $orders = Order::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('SUM(total_price) as total_sum')
        )
            ->whereYear('created_at', now()->year)
            ->groupBy('month')
            ->pluck('total_sum', 'month'); // [1 => 2433, 2 => 3454, ...]

        // Заполним все 12 месяцев, даже если нет заказов
        $data = [];
        for ($i = 1; $i <= 12; $i++) {
            $data[] = (float) ($orders[$i] ?? 0);
        }
        return [
            'datasets' => [
                [
                    'label' => 'Сумма заказов',
                    'data' => $data,
                    'backgroundColor' => 'rgba(75, 192, 192, 0.5)',
                    'borderColor' => 'rgba(75, 192, 192, 1)',
                ],
            ],
            'labels' => [
                'Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июнь',
                'Июль', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек',
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
