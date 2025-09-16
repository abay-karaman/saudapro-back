<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Filament\Resources\OrderResource\Widgets\OrderStatsWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ListRecords\Tab;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            OrderStatsWidget::class,
        ];
    }

    public function getTabs(): array
    {
        return [
            'Все' => Tab::make(),
            'Новые' => Tab::make()->modifyQueryUsing(function ($query) {
                $query->where('status', 'new');
            }),
            'В обработке' => Tab::make()->modifyQueryUsing(function ($query) {
                $query->where('status', 'in_progress');
            }),
            'Собрано' => Tab::make()->modifyQueryUsing(function ($query) {
                $query->where('status', 'collected');
            }),
            'Отгружено' => Tab::make()->modifyQueryUsing(function ($query) {
                $query->where('status', 'loaded');
            }),
            'В пути' => Tab::make()->modifyQueryUsing(function ($query) {
                $query->where('status', 'on_way');
            }),
            'Завершенные' => Tab::make()->modifyQueryUsing(function ($query) {
                $query->where('status', 'delivered');
            }),
            'Отмененные' => Tab::make()->modifyQueryUsing(function ($query) {
                $query->where('status', 'cancelled');
            }),
        ];
    }
}
