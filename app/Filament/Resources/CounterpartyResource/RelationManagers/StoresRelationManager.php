<?php

namespace App\Filament\Resources\CounterpartyResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StoresRelationManager extends RelationManager
{
    protected static string $relationship = 'stores';
    protected static ?string $title = 'Адреса доставки';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')->maxLength(255)->label('Название адреса'),
                TextInput::make('code')->readOnly()->label('Код 1С'),
                TextInput::make('phone')->label('Телефон N'),
                TextInput::make('address')->label('Адрес'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->label('Название адреса'),
                Tables\Columns\TextColumn::make('code')->toggleable()->label('Код 1С'),
                Tables\Columns\TextColumn::make('phone')->label('Телефон N'),
                Tables\Columns\TextColumn::make('address')->label('Адрес'),
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
