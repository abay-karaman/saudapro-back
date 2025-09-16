<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CounterpartyResource\Pages;
use App\Filament\Resources\CounterpartyResource\RelationManagers;
use App\Filament\Resources\CounterpartyResource\RelationManagers\StoresRelationManager;
use App\Filament\Resources\CounterpartyResource\Widgets\CounterpartyWidget;
use App\Models\Counterparty;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CounterpartyResource extends Resource
{
    protected static ?string $model = Counterparty::class;

    public static function getModelLabel(): string
    {
        return 'контрагент';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Контрагенты';
    }

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Основная информация')->schema([
                    TextInput::make('name')->label('Название контрагента'),
                    TextInput::make('id')->readOnly()->label('Номер ID'),
                    TextInput::make('code')->readOnly()->label('Код с 1С'),
                    TextInput::make('bin_iin')->label('ИИН-БИН'),
                    TextInput::make('phone')->label('Тел номер'),
                ])->columnSpan(2),
                Section::make('Связи')->schema([
                    Select::make('user_id')
                        ->label('Клиент')
                        ->relationship('user', 'name')
                        ->preload()
                        ->searchable(),
                    Select::make('representatives')
                        ->label('Торговые')
                        ->relationship('representatives', 'name', fn ($query) =>
                        $query->where('role_id', 2)) // фильтруем только пользователей с ролью 2)
                        ->multiple()
                        ->preload()
                        ->searchable(),
                ])->columnSpan(1),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('Номер ID'),
                TextColumn::make('name')->searchable()->label('Название'),
                TextColumn::make('code')->toggleable()->label('Код с 1С'),
                TextColumn::make('bin_iin')->searchable()->toggleable()->label('ИИН-БИН'),
                TextColumn::make('phone')->searchable()->label('Тел номер'),
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
            StoresRelationManager::class,
            RelationManagers\OrdersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCounterparties::route('/'),
            'create' => Pages\CreateCounterparty::route('/create'),
            'edit' => Pages\EditCounterparty::route('/{record}/edit'),
        ];
    }
}
