<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MainWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Админы', User::where('role_id', 1)->count())
                ->description('Количество админов в системе')
                ->descriptionIcon('heroicon-o-shield-check'),

            Stat::make('Торговые', User::where('role_id', 2)->count())
                ->description('Количество торговых представителей в системе')
                ->descriptionIcon('heroicon-o-at-symbol'),

            Stat::make('Экспедиторы', User::where('role_id', 3)->count())
                ->description('Количество всех экспедиторов в системе')
                ->descriptionIcon('heroicon-o-truck'),

            Stat::make('Клиенты', User::where('role_id', 4)->count())
                ->color('success')
                ->description('Количество всех клиентов в системе')
                ->descriptionIcon('heroicon-o-users', IconPosition::Before),
        ];
    }
}
