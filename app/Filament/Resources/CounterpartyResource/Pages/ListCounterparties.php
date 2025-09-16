<?php

namespace App\Filament\Resources\CounterpartyResource\Pages;

use App\Filament\Resources\CounterpartyResource;
use App\Filament\Resources\CounterpartyResource\Widgets\CounterpartyWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCounterparties extends ListRecords
{
    protected static string $resource = CounterpartyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }


    public function getHeaderWidgets(): array
    {
        return [
            CounterpartyWidget::class
        ];
    }
}
