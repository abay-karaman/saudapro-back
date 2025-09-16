<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Filament\Resources\ClientResource\RelationManagers;
use App\Models\User;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;

class ClientResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationGroup = 'Пользователи';
    protected static ?int $navigationSort = 4;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $label = 'Клиент';
    protected static ?string $pluralLabel = 'Клиенты';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()->schema([
                    TextInput::make('name')->rules('min:3')->required()->label('Имя'),
                    TextInput::make('phone')->tel()->label('Телефон №')
                        ->unique(ignoreRecord: true),
                    Select::make('counterparty.user_id')
                        ->label('Контрагент')
                        ->relationship('counterparty', 'name') // 'name' — колонка из counterparty
                        ->searchable()
                        ->preload(),
                ])->columnSpan(2),

                Section::make()->schema([
                    Select::make('status')
                        ->label('Статус')
                        ->options([
                            'new' => 'Новый',
                            'waiting' => 'Неподтвержден',
                            'active' => 'Активный',
                        ]),
                    Select::make('role_id')
                        ->label('Роль')
                        ->relationship('role', 'name'),
                    Select::make('price_type_id')
                        ->label('Тип цены')
                        ->relationship('priceType', 'name'),
                ])->columnSpan(1)
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('ID'),
                TextColumn::make('name')->searchable()->label('Имя'),
                TextColumn::make('phone')->toggleable()->label('Телефон'),
                TextColumn::make('status')->sortable()->label('Статус'),
                TextColumn::make('role.name')->sortable()->label('Роль'),
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
            'index' => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            'edit' => Pages\EditClient::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): string
    {
        $count = User::where('status', 'waiting')->where('role_id', 4)->count();
        return $count === 0 ? false : $count;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('role_id', 4);
    }
}
