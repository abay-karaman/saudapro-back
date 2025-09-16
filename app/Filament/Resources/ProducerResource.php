<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProducerResource\Pages;
use App\Filament\Resources\ProducerResource\RelationManagers;
use App\Models\Producer;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProducerResource extends Resource
{
    protected static ?string $model = Producer::class;
    public static function getModelLabel(): string    {return 'производитель';}

    public static function getPluralModelLabel(): string    {return 'Производители';}
    protected static ?string $navigationGroup = 'Каталог';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')->label('Название'),
                TextInput::make('code')->label('Код с 1С'),
                TextInput::make('country')->label('Страна'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Название'),
                TextColumn::make('code')->label('Код с 1С'),
                TextColumn::make('country')->label('Страна'),
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
            'index' => Pages\ListProducers::route('/'),
            'create' => Pages\CreateProducer::route('/create'),
            'edit' => Pages\EditProducer::route('/{record}/edit'),
        ];
    }
}
