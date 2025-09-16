<?php

namespace App\Filament\Resources\CounterpartyResource\Widgets;

use App\Models\Counterparty;
use App\Models\Store;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CounterpartyWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Количество контрагентов', Counterparty::count()),
            Stat::make('Количество адресов', Store::count())
        ];
    }
}
