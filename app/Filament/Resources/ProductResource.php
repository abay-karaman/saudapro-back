<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use App\Models\Unit;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\CheckboxColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    public static function getModelLabel(): string
    {
        return 'Товар';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Товары';
    }

    protected static ?string $navigationGroup = 'Каталог';
    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()->schema([
                    TextInput::make('name')->label('Наименование'),
                    TextInput::make('id')->readOnly()->label('Номер ID'),
                    TextInput::make('code')->label('Код с 1С'),
                    Select::make('status')
                        ->label('Статус')
                        ->options([
                            'old' => 'Обычный',
                            'new' => 'Новый',
                            'bestseller' => 'Хит продаж',
                            'sale' => 'Распродажа',
                        ]),
                    Select::make('category_id')
                        ->label('Категория')
                        ->relationship('category', 'name')
                        ->searchable()
                        ->required(),

                    Select::make('unit_id')
                        ->label('Единица')
                        ->options(Unit::all()->pluck('name', 'id')),
                ])->columns(3),
                Section::make('Цены')->schema([
                    Repeater::make('prices')
                        ->relationship('prices')
                        ->schema([
                            TextInput::make('price_type_id')
                                ->label('Тип цены')
                                ->disabled()
                                ->afterStateHydrated(function ($component, $state, $record) {
                                    $component->state($record->priceType->name ?? '');
                                }),

                            TextInput::make('price')
                                ->label('Цена')
                                ->numeric()
                                ->prefix('₸')
                                ->required(),
                        ])
                        ->columns(2)
                        ->deletable(false)
                        ->grid(2),
                ])->columnSpan(3),

                MarkdownEditor::make('description')->label('Описание товара')->columnSpan(3),
                //Toggle::make('is_active')->label('Активировать'),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->toggleable(isToggledHiddenByDefault: true)->label('Id'),
                TextColumn::make('name')->searchable(isIndividual: true)->label('Наименование'),

                Tables\Columns\TextColumn::make('images')
                    ->label('Фото товара')
                    ->toggleable()
                    ->extraAttributes(['class' => 'w-48 overflow-x-auto',])
                    ->formatStateUsing(fn($record) =>
                        "<div class='flex flex-row gap-1'>" .
                        $record->images
                            ->map(fn($img) => "<img src='" . Storage::disk('s3')->url($img->image_path) . "' class='w-12 h-12 rounded-md inline-block mr-1' />"
                            )
                            ->implode('')
                        . "</div>"
                    )
                    ->html(), // обязательно, иначе HTML не отрендерится

                TextColumn::make('code')->sortable()->searchable(isIndividual: true)->toggleable()->label('Код с 1С'),
                TextColumn::make('status')->searchable()->toggleable()->label('Статус'),
                TextColumn::make('category.name')->searchable()->label('Категория'),
                CheckboxColumn::make('is_active')->label('Активировать'),
            ])->defaultSort('name')
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Активен'),
                SelectFilter::make('category_id')
                    ->label('Категория')
                    ->searchable()
                    ->preload()
                    ->relationship('category', 'name')
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
            RelationManagers\ImagesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): string
    {
        $active = Product::where('is_active', true)->count();
        $total = Product::count();

        return (string)"{$active} / {$total}";
    }
}
