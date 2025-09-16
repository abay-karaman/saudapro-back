<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';
    protected static ?string $title = 'Оплаты';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('paid_amount')
                    ->label('Сумма оплаты')
                    ->numeric()
                    ->required(),

                Forms\Components\TextInput::make('debt_amount')
                    ->label('Сумма долга')
                    ->numeric()
                    ->required(),

                Forms\Components\DateTimePicker::make('paid_at')
                    ->label('Дата оплаты')
                    ->default(now()),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('paid_amount')
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID'),
                Tables\Columns\TextColumn::make('courier.name')->label('Курьер'),
                Tables\Columns\TextColumn::make('order.total_delivered')->label('Сумма')->money('KZT'),
                Tables\Columns\TextColumn::make('paid_amount')->label('Оплачено')->money('KZT'),
                Tables\Columns\TextColumn::make('debt_amount')->label('Остаток')->money('KZT'),
                Tables\Columns\IconColumn::make('debt_confirmed')->boolean()->label('Долг подтвержден'),
                Tables\Columns\TextColumn::make('paid_at')->label('Дата')->dateTime('d.m.Y H:i'),
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
