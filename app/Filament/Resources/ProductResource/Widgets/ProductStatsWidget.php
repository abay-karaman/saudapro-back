<?php

namespace App\Filament\Resources\ProductResource\Widgets;

use App\Models\Product;
use App\Models\ProductStock;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ProductStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Количество товаров', Product::count()),
            Stat::make('Остаток товаров', (int)ProductStock::sum('stock')),
        ];
    }
}
