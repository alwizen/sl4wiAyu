<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CashTransactionResource\Pages;
use App\Filament\Resources\CashTransactionResource\RelationManagers;
use App\Models\CashTransaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class CashTransactionResource extends Resource
{
    protected static ?string $model = CashTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Transaksi')
                    ->schema([
                        Forms\Components\TextInput::make('transaction_code')
                            ->label('Kode Transaksi')
                            ->default(fn() => 'SPPG-' . str_pad(random_int(0, 99999), 4, '0', STR_PAD_LEFT))
                            ->disabled()
                            ->dehydrated()
                            ->required(),

                        Forms\Components\DatePicker::make('transaction_date')
                            ->default(now())
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Kategori & Jumlah')
                    ->schema([
                        Forms\Components\Select::make('category_type')
                            ->label('Tipe Kategori')
                            ->options([
                                'income' => 'Pemasukan',
                                'expense' => 'Pengeluaran',
                            ])
                            ->reactive()
                            ->afterStateUpdated(fn(callable $set) => $set('category_id', null)),

                        Forms\Components\Select::make('category_id')
                            ->label('Kategori')
                            ->options(function (callable $get) {
                                $type = $get('category_type');

                                if (!$type) {
                                    return [];
                                }

                                return \App\Models\CashCategory::where('type', $type)
                                    ->pluck('name', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nama Kategori')
                                    ->maxLength(255),
                                Forms\Components\Select::make('type')
                                    ->required()
                                    ->label('Tipe Kategori')
                                    ->options([
                                        'income' => 'Pemasukan',
                                        'expense' => 'Pengeluaran',
                                    ]),
                            ]),
                        Forms\Components\TextInput::make('amount')
                            ->required()
                            ->prefix('Rp')
                            ->numeric(),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Keterangan Tambahan')
                    ->schema([
                        Forms\Components\Textarea::make('description'),

                        Forms\Components\Select::make('methode')
                            ->label('Metode Pembayaran')
                            ->options([
                                'cash' => 'Tunai',
                                'transfer' => 'Transfer',
                            ])
                            ->default('cash'),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('transaction_code')
                    ->searchable()
                    ->label('Kode Transaksi')
                    ->description(fn($record) => $record->description),
                Tables\Columns\TextColumn::make('transaction_date')
                    ->date()
                    ->label('Tanggal')
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->numeric()
                    ->label('Nama Kategori')
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.type')
                    ->label('Tipe Kategori')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'income' => 'success',
                        'expense' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Jumlah')
                    ->numeric()
                    ->summarize(Sum::make())
                    ->prefix('Rp')
                    ->visible(fn(Builder $query): bool => $query->exists())
                    ->sortable(),

                Tables\Columns\TextColumn::make('methode')
                    ->label('Pembayaran')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])


            ->filters([
                SelectFilter::make('category')
                    ->relationship('category', 'type'),

            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    ExportBulkAction::make()

                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageCashTransactions::route('/'),
        ];
    }
}
