<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CourierResource\Pages;
use App\Filament\Resources\CourierResource\RelationManagers;
use App\Models\User;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CourierResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationGroup = 'Пользователи';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationIcon  = 'heroicon-o-truck';
    protected static ?string $label = 'Экспедитор';
    protected static ?string $pluralLabel = 'Экспедиторы';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()->schema([
                    TextInput::make('name')->rules('min:3')->required()->label('Имя'),
                    TextInput::make('phone')->unique(ignoreRecord: true)->tel()->label('Телефон №'),
                ])->columnSpan(2),

                Section::make()->schema([
                    Select::make('status')
                        ->label('Статус')
                        ->options([
                            'new' => 'Новый',
                            'waiting' => 'Неактивный',
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
                SelectFilter::make('status')
                    ->label('Статус')
                    ->options([
                        'waiting' => 'Неактивные',
                        'active' => 'Активные',
                    ])
                    ->default('active'),
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
            'index' => Pages\ListCouriers::route('/'),
            'create' => Pages\CreateCourier::route('/create'),
            'edit' => Pages\EditCourier::route('/{record}/edit'),
        ];
    }
    public static function getNavigationBadge(): string
    {
        $count = User::where('status', 'waiting')->where('role_id', 3)->count();
        return $count === 0 ? false  : $count;
    }
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('role_id', 3);
    }
}
