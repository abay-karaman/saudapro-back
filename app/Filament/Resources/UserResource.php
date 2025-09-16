<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationGroup = 'Пользователи';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationIcon  = 'heroicon-o-shield-check';
    protected static ?string $label = 'Администратор';
    protected static ?string $pluralLabel = 'Администраторы';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()->schema([
                    TextInput::make('name')->rules('min:3')->required()->label('Имя'),
                    TextInput::make('phone')->unique(ignoreRecord: true)->tel()->label('Телефон №'),
                    TextInput::make('email')->email()->required()->label('Почта'),
                    TextInput::make('password')->password()->required()->label('Пароль'),
                ])->columnSpan(2),

                Section::make()->schema([
                    Select::make('status')
                        ->label('Статус')
                        ->required()
                        ->options([
                            'new' => 'Новый',
                            'waiting' => 'Неподтвержден',
                            'active' => 'Активный',
                        ]),
                    Select::make('role_id')
                        ->required()
                        ->label('Роль')
                        ->relationship('role', 'name'),
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
                TextColumn::make('email')->searchable()->label('Почта'),
                TextColumn::make('status')->sortable()->label('Статус'),
                TextColumn::make('role.name')->sortable()->label('Роль'),
            ])
            ->filters([
                //
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }


    public static function getNavigationBadge(): string
    {
        $count = User::where('status', 'waiting')->where('role_id', 1)->count();
        return $count === 0 ? false  : $count;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('role_id', 1);
    }
}
