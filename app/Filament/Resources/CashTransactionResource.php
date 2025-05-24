<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CashTransactionResource\Pages;
use App\Filament\Resources\CashTransactionResource\RelationManagers;
use App\Models\CashCategory;
use App\Models\CashTransaction;
use App\Models\PurchaseOrder;
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

//    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Keuangan';


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
                                if (!$type) return [];

                                return CashCategory::where('type', $type)
                                    ->pluck('name', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->reactive()
                            ->required(),

                        Forms\Components\Select::make('purchase_order_id')
                            ->label('Nomor Purchase Order')
                            ->options(function () {
                                return PurchaseOrder::where('payment_status', 'paid')
                                    ->orderBy('order_date', 'desc')
                                    ->get()
                                    ->mapWithKeys(fn($po) => [
                                        $po->id => "{$po->order_number} - {$po->order_date->format('d M Y')}"
                                    ]);
                            })
                            ->searchable()
                            ->preload()
                            ->reactive()
                            ->hidden(function (callable $get) {
                                $type = $get('category_type');
                                $categoryId = $get('category_id');

                                if ($type !== 'expense' || !$categoryId) {
                                    return true;
                                }

                                $category = CashCategory::find($categoryId);
                                return $category?->slug !== 'pembayaran-po';
                            })
                            ->afterStateUpdated(function (callable $set, $state) {
                                $po = PurchaseOrder::find($state);
                                if ($po) {
                                    $set('amount', $po->total_amount);
                                }
                            }),

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
                    ->prefix('Rp')
                    ->summarize([
                        Sum::make()
                            ->label('Total')
                            ->numeric()
                            ->prefix('Rp')
                    ])
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
            ->groups([
                Tables\Grouping\Group::make('category.type')
                    ->label('Tipe Kategori')
                    ->collapsible()
                    ->titlePrefixedWithLabel(false)
                    ->getTitleFromRecordUsing(fn($record) => match($record->category->type) {
                        'income' => '💰 Pemasukan',
                        'expense' => '💸 Pengeluaran',
                        default => $record->category->type
                    }),
            ])
            ->defaultGroup('category.type')
            ->groupedBulkActions([
                Tables\Actions\DeleteBulkAction::make(),
                ExportBulkAction::make(),
            ])
            ->filters([
                SelectFilter::make('category_type')
                    ->label('Tipe Kategori')
                    ->options([
                        'income' => 'Pemasukan',
                        'expense' => 'Pengeluaran',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn (Builder $query, $value): Builder => $query->whereHas('category', fn (Builder $query) => $query->where('type', $value))
                        );
                    }),

                Tables\Filters\Filter::make('transaction_date')
                    ->form([
                        Forms\Components\DatePicker::make('date_from')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('date_until')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('transaction_date', '>=', $date),
                            )
                            ->when(
                                $data['date_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('transaction_date', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['date_from'] ?? null) {
                            $indicators[] = 'Dari: ' . \Carbon\Carbon::parse($data['date_from'])->format('d M Y');
                        }
                        if ($data['date_until'] ?? null) {
                            $indicators[] = 'Sampai: ' . \Carbon\Carbon::parse($data['date_until'])->format('d M Y');
                        }
                        return $indicators;
                    }),
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
