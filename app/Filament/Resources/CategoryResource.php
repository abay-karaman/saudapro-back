<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Filament\Resources\CategoryResource\RelationManagers\ProductsRelationManager;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\CheckboxColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    public static function getModelLabel(): string
    {
        return 'Категория';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Категории';
    }

    protected static ?string $navigationGroup = 'Каталог';
    protected static ?string $navigationIcon = 'heroicon-o-folder';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')->label('Наименование'),
                TextInput::make('id')->readOnly()->label('Номер ID'),
                TextInput::make('code')->label('Код с 1С'),
                FileUpload::make('icon')
                    ->label('Иконка категории')
                    ->disk('s3')
                    ->image()
                    ->maxSize(2048) // 2MB
                    ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/jpg'])
                    ->directory(fn($get) => 'categories/77789888885/' . $get('code'))
                    ->visibility('public')
                    ->preserveFilenames()
                    ->enableDownload()
                    ->enableOpen(),
                Select::make('parent_id')
                    ->options(function () {
                        return self::getCategoriesTree(Category::all());
                    })
                    ->disableOptionWhen(function (Forms\Get $get, string $value) {
                        return $value == $get('id');
                    })
                    ->label('Родительская категория')
                    ->searchable()
                    ->placeholder('Корневая категория'),
                Toggle::make('is_active')
                    ->label('Активировать'),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('ID'),
                TextColumn::make('name')
                    ->sortable()
                    ->searchable()
                    ->label('Наименование'),
                TextColumn::make('code')
                    ->searchable()
                    ->label('Код с 1С'),
                ImageColumn::make('icon')
                    ->label('Превью')
                    ->disk('s3')
                    ->visibility('public')
                    ->toggleable()
                    ->height(80),
                TextColumn::make('parent.name')
                    ->searchable()
                    ->label('Родитель'),
                IconColumn::make('is_active')
                    ->boolean()
                    ->label('Активен')
                    ->sortable()
                    ->toggleable(),
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
            ProductsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }

    public static function getCategoriesTree($categories, $parentId = null, $depth = 0): array
    {
        $options = [];
        foreach ($categories->where('parent_id', $parentId) as $category) {
            $prefix = str_repeat(' -', $depth);
            $options[$category->id] = $prefix . $category->name;
            $children = self::getCategoriesTree($categories, $category->id, $depth + 1);
            $options += $children;
        }
        return $options;
    }
}
