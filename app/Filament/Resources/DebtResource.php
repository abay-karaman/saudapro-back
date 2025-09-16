<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DebtResource\Pages;
use App\Models\OrderPayment;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\DebtResource\Widgets\DebtStats;

class DebtResource extends Resource
{
    protected static ?string $model = OrderPayment::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Должники';
    protected static ?string $pluralModelLabel = 'Должники';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Placeholder::make('order_total_delivered')
                ->label('Сумма заказа')
                ->content(function ($record, callable $set) {
                    $total = $record?->order?->total_delivered ?? '-';
                    $set('total_delivered', $total);
                    return $total;
                })
                ->dehydrated(false), // не сохранять в базу

                Forms\Components\Select::make('order_id')
                    ->label('Заказ')
                    ->relationship('order', 'id')
                    ->disabled() // чтобы нельзя было менять
                    ->dehydrated(false), // не пытаться сохранять изменения обратно

                Forms\Components\Hidden::make('total_delivered'),

                Forms\Components\TextInput::make('paid_amount')
                    ->label('Оплачено')
                    ->numeric()
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        $total = $get('total_delivered') ?? 0;
                        $set('debt_amount', max($total - (float)$state, 0));
                    }),

                Forms\Components\TextInput::make('debt_amount')
                    ->label('Долг')
                    ->numeric()
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        $total = $get('total_delivered') ?? 0;
                        $set('paid_amount', max($total - (float)$state, 0));
                    }),

                Forms\Components\Toggle::make('debt_confirmed')
                    ->label('Долг подтвержден'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(fn() => OrderPayment::query()
                ->with(['order', 'counterparty', 'courier'])
                ->where('debt_amount', '>', 0)
                ->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('order_id')
                    ->label('ID заказа'),

                Tables\Columns\TextColumn::make('counterparty.name')
                    ->label('Контрагент')
                    ->searchable(),

                Tables\Columns\TextColumn::make('courier.name')
                    ->label('Курьер')
                    ->searchable(),

                Tables\Columns\TextColumn::make('debt_amount')
                    ->label('Сумма долга')
                    ->summarize(Tables\Columns\Summarizers\Sum::make())
                    ->sortable()
                    ->money('KZT'),

                Tables\Columns\TextColumn::make('order.total_delivered')
                    ->label('Сумма заказа')
                    ->sortable()
                    ->money('KZT'),

                Tables\Columns\IconColumn::make('debt_confirmed')
                    ->boolean()
                    ->label('Долг подтвержден'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('courier_id')
                    ->label('Курьер')
                    ->options(fn() => User::where('role_id', 3)->pluck('name', 'id')
                    )
                    ->query(function ($query, array $data) {
                        if (!empty($data['value'])) {
                            $query->where('courier_id', $data['value']);
                        }
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListDebts::route('/'),
            'edit' => Pages\EditDebt::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): string
    {
        $debts = OrderPayment::where('debt_amount', '>', 0)->count();
        $debts_not_confirmed = OrderPayment::where('debt_amount', '>', 0)->where('debt_confirmed', 0)->count();
        return (string) "{$debts} : {$debts_not_confirmed}";
    }
}
