<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TruckResource\Pages;
use App\Filament\Resources\TruckResource\RelationManagers;
use App\Models\Truck;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TruckResource extends Resource
{
    protected static ?string $model = Truck::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $label = 'Техника';
    protected static ?string $pluralLabel = 'Техника';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Модель')
                    ->required()
                    ->maxLength(191),
                Forms\Components\TextInput::make('code')
                    ->label('Код 1С')
                    ->maxLength(191),
                Forms\Components\TextInput::make('number')
                    ->label('Гос номер')
                    ->required()
                    ->maxLength(191),
                Forms\Components\TextInput::make('capacity')
                    ->label('Объем в м3')
                    ->maxLength(191),
                Forms\Components\TextInput::make('payload')
                    ->label('Грзоподъемность в тн')
                    ->maxLength(191),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Модель')
                    ->searchable(),
                Tables\Columns\TextColumn::make('code')
                    ->label('Код 1С')
                    ->searchable(),
                Tables\Columns\TextColumn::make('number')
                    ->label('Гос номер')
                    ->searchable(),
                Tables\Columns\TextColumn::make('capacity')
                    ->label('Объем в м3'),
                Tables\Columns\TextColumn::make('payload')
                    ->label('Грзоподъемность в тн'),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTrucks::route('/'),
            'create' => Pages\CreateTruck::route('/create'),
            'edit' => Pages\EditTruck::route('/{record}/edit'),
        ];
    }
}
