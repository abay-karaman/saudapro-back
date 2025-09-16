<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ListRecords\Tab;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'Все' => Tab::make(),
            'Новые' => Tab::make()->modifyQueryUsing(function ($query) {
                $query->where('status', 'new');
            }),
            'Неподтвержденные' => Tab::make()->modifyQueryUsing(function ($query) {
                $query->where('status', 'waiting');
            }),
            'Активные' => Tab::make()->modifyQueryUsing(function ($query) {
                $query->where('status', 'active');
            }),
        ];
    }
}
