<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Log;

class ImagesRelationManager extends RelationManager
{
    protected static string $relationship = 'images'; // hasMany(ProductImage)
    protected static ?string $title = 'Фотографии и видео';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // Здесь можно оставить только для редактирования существующих записей
                Forms\Components\FileUpload::make('files')
                    ->label('Файлы')
                    ->disk('s3')
                    ->visibility('public')
                    ->directory('products/77789888885/'.$this->ownerRecord->code)
                    ->maxSize(5120)
                    ->imagePreviewHeight('200')
                    ->enableOpen()
                    ->enableDownload()
                    ->multiple()
                    ->storeFileNamesIn('original_name')
                    ->acceptedFileTypes([
                        'image/jpeg', 'image/png', 'image/gif',
                        'video/mp4', 'video/webm', 'video/quicktime',
                    ])
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->label('Превью')
                    ->disk('s3')
                    ->visibility('public')
                    ->height(80),

                Tables\Columns\TextColumn::make('type')
                    ->label('Тип')
                    ->badge(),

                Tables\Columns\IconColumn::make('is_main')
                    ->boolean()
                    ->label('Главное'),
            ])
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->headerActions([
                // Кастомный Action для загрузки файлов
                Tables\Actions\Action::make('uploadFiles')
                    ->label('Добавить файлы')
                    ->form([
                        Forms\Components\FileUpload::make('files')
                            ->multiple()
                            ->disk('s3')
                            ->directory(fn() => 'products/77789888885/'.$this->ownerRecord->code)
                            ->storeFileNamesIn('original_name')
                            ->acceptedFileTypes(['image/jpeg','image/png','image/gif','video/mp4','video/webm','video/quicktime'])
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $owner = $this->ownerRecord;

                        if (!$owner) {
                            Log::error('ownerRecord is null in uploadFiles!');
                            return;
                        }

                        $isFirst = $owner->images()->count() === 0;

                        foreach ($data['files'] as $path) {
                            Log::info('Creating image', ['path' => $path]);
                            $owner->images()->create([
                                'image_path' => $path,
                                'type'       => $this->detectType($path),
                                'is_main'    => $isFirst,
                                'sort_order' => $this->getNextSortOrder(),
                            ]);
                            $isFirst = false;
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->form([
                        Forms\Components\Toggle::make('is_main')
                            ->label('Главное фото')
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, $record) {
                                if ($state && $record) {
                                    $owner = $this->ownerRecord ?? null;

                                    if (!$owner) {
                                        Log::error('ownerRecord is null in afterStateUpdated!');
                                        return;
                                    }

                                    $owner->images()
                                        ->where('id', '!=', $record->id)
                                        ->update(['is_main' => false]);
                                }
                            }),
                    ]),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    private function detectType(string $path): string
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $videoExt = ['mp4', 'webm', 'mov', 'quicktime'];

        return in_array($ext, $videoExt) ? 'video' : 'image';
    }

    private function getNextSortOrder(): int
    {
        if (!$this->ownerRecord) {
            Log::error('ownerRecord is null in getNextSortOrder!');
            return 1;
        }

        return ($this->ownerRecord->images()->max('sort_order') ?? 0) + 1;
    }
}
