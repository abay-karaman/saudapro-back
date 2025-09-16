<?php

namespace App\Filament\Resources\ManagerResource\RelationManagers;

use App\Models\Counterparty;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CounterpartiesRelationManager extends RelationManager
{
    protected static string $relationship = 'counterparties';
    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $title = 'Контрагенты данного торгового';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')->label('Название контрагента'),
                TextInput::make('id')->readOnly()->label('Номер ID'),
                TextInput::make('code')->readOnly()->label('Код с 1С'),
                TextInput::make('bin_iin')->label('ИИН-БИН'),
                TextInput::make('phone')->label('Тел номер'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
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
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label('Прикрепить контрагента')
                    ->preloadRecordSelect()
                    ->recordSelect(function (Select $select) {
                        return $select
                            ->label('Контрагент')
                            ->searchable()
                            ->options(
                                Counterparty::query()
                                    ->pluck('name', 'id')
                            );
                    })
            ])
            ->actions([
                Tables\Actions\DetachAction::make()
            ])
            ->bulkActions([
                //
            ]);
    }
}
