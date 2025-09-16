<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';
    protected static ?string $title = 'Детали заказа';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('product_name')
                    ->label('Товар')
                    ->disabled()
                    ->afterStateHydrated(function ($component, $state, $record) {
                        $component->state($record->product->name ?? '');
                    }),
                TextInput::make('quantity')->label('Количество'),
                TextInput::make('price')->label('Цена')->disabled()
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('order_id')
            ->columns([
                TextColumn::make('order_id')->label('ID заказа'),
                TextColumn::make('product.name')->label('Товар'),
                TextColumn::make('quantity')->label('Количестов'),
                TextColumn::make('price')->money('KZT')->label('Цена'),
                TextColumn::make('total')
                    ->label('Сумма')
                    ->state(fn ($record) => $record->quantity * $record->price)
                    ->money('KZT'),

            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
