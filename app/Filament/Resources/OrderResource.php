<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers\ItemsRelationManager;
use App\Filament\Resources\OrderResource\RelationManagers\PaymentsRelationManager;
use App\Models\Order;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationGroup;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    public static function getModelLabel(): string
    {
        return 'Заказ';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Заказы';
    }

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('counterparty_name')
                    ->label('Контрагент')
                    ->disabled()
                    ->afterStateHydrated(function ($component, $state, $record) {
                        $component->state($record->counterparty->name ?? '');
                    }),
                TextInput::make('store_name')
                    ->label('Адрес')
                    ->disabled()
                    ->afterStateHydrated(function ($component, $state, $record) {
                        $component->state($record->store->address ?? '');
                    }),
                TextInput::make('store_user_name')
                    ->label('Торговый')
                    ->disabled()
                    ->afterStateHydrated(function ($component, $state, $record) {
                        $component->state($record->representative->name ?? '');
                    }),
                TextInput::make('comment')->label('Комментарий'),
                TextInput::make('total_price')->label('Сумма'),
                Select::make('status')
                    ->label('Статус')
                    ->options([
                        'new' => 'Новый',
                        'in_progress' => 'В обратотке',
                        'collected' => 'Собран',
                        'loaded' => 'Загружен',
                        'on_way' => 'В пути',
                        'delivered'  => 'Доставлен',
                        'cancelled'  => 'Отменен',
                    ]),
                DateTimePicker::make('created_at')
                    ->displayFormat('d.m.Y H:i'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('Номер заказа'),
                TextColumn::make('store.counterparty.name')->label('Имя контрагента'),
                TextColumn::make('store.name')->label('Магазин'),
                TextColumn::make('total_price')->label('Сумма')->money('KZT'),
                TextColumn::make('status')->label('Статус'),
                TextColumn::make('created_at')->dateTime('d.m.Y H:i')->label('Создан'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationGroup::make('Детали заказа', [
                ItemsRelationManager::class,
                PaymentsRelationManager::class,
            ]),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): string
    {
        return (string) Order::where('status', 'new')->count();
    }
}
