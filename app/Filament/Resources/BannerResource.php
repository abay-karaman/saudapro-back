<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BannerResource\Pages;
use App\Filament\Resources\BannerResource\RelationManagers;
use App\Models\Banner;
use Filament\Forms;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\CheckboxColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BannerResource extends Resource
{
    protected static ?string $model = Banner::class;
    public static function getModelLabel(): string
    {return 'баннер';}

    public static function getPluralModelLabel(): string
    {return 'Баннеры';}
    protected static ?string $navigationGroup = 'Контент';
    protected static ?string $navigationIcon = 'heroicon-o-photo';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')->label('Название'),
                TextInput::make('id')->label('Номер ID')->readonly(),
                TextInput::make('link')->label('Ссылка'),

                Select::make('type')
                    ->label('Место вывода')
                    ->options([
                        'main' => 'Главная страница',
                        'catalog' => 'Каталог',
                        'promo' => 'Промоакции',
                    ]),
                FileUpload::make('image_path')
                    ->label('Фото баннера')
                    ->disk('s3')
                    ->image()
                    ->maxSize(2048) // 2MB
                    ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/jpg'])
                    ->directory(fn($get) => 'banners/77789888885/' . $get('type'))
                    ->visibility('public')
                    ->preserveFilenames()
                    ->enableDownload()
                    ->enableOpen(),
                Checkbox::make('is_active')->label('Активировать')->default(true)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('Номер ID'),
                TextColumn::make('title')->label('Название'),
                TextColumn::make('link')->label('Ссылка'),
                TextColumn::make('type')->label('Место'),
                CheckboxColumn::make('is_active')->label('Активен')->default(true)
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
            'index' => Pages\ListBanners::route('/'),
            'create' => Pages\CreateBanner::route('/create'),
            'edit' => Pages\EditBanner::route('/{record}/edit'),
        ];
    }
}
