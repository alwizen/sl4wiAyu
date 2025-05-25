<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WarehouseItemResource\Pages;
use App\Filament\Resources\WarehouseItemResource\RelationManagers;
use App\Models\WarehouseItem;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Notifications\Notification;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Filament\Tables\Actions\DeleteAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class WarehouseItemResource extends Resource
{
    protected static ?string $model = WarehouseItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-circle-stack';

    protected static ?string $navigationGroup = 'Gudang';

    protected static ?string $navigationLabel = 'Daftar Barang';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Informasi Barang')
                ->description('Detail informasi barang')
                ->columns(2)
                ->schema([
                    Select::make('warehouse_category_id')
                        ->relationship('category', 'name')
                        ->label('Kategori')
                        ->required()
                        ->columnSpan(1)
                        ->createOptionForm([
                            TextInput::make('name')->label('Nama Kategori')->required(),
                        ]),

                    TextInput::make('name')
                        ->label('Nama Barang')
                        ->required()
                        ->columnSpan(1),
                ]),

            Section::make('Detail Stok')
                ->description('Informasi satuan dan stok barang')
                ->columns(2)
                ->schema([
                    TextInput::make('unit')
                        ->label('Satuan (kg, liter, pcs)')
                        ->required()
                        ->columnSpan(1),

                    TextInput::make('stock')
                        ->label('Stok')
                        ->numeric()
                        ->default(0)
                        ->disabled() // input hanya dari proses lain
                        ->columnSpan(1),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Item')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('category.name')
                    ->label('Kategori')
                    ->sortable()
                    ->searchable(),

                // TextColumn::make('unit')
                //     ->label('Satuan'),

                TextColumn::make('stock')
                    ->label('Stok')
                    ->numeric()
                    ->formatStateUsing(fn($state, $record) => number_format($state, 0, ',', '.') . ' ' . $record->unit)
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Stok Diperbarui pada')
                    //                    ->dateTime('d M Y')
                    ->sortable(),
                //                    ->toggleable(isToggledHiddenByDefault: true)
            ])
            ->defaultSort('name')
            ->filters([
                Tables\Filters\SelectFilter::make('warehouse_category_id')
                    ->options([
                        '1' => 'Kering',
                        '2' => 'Basah',
                        '3' => 'Bumbu',
                    ])
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                DeleteAction::make()
                    ->before(function (DeleteAction $action, WarehouseItem $record) {
                        // Cek apakah item masih digunakan
                        $relatedCount = DB::table('purchase_order_items')
                            ->where('item_id', $record->id)
                            ->count();

                        if ($relatedCount > 0) {
                            // Batalkan action dan tampilkan notifikasi
                            Notification::make()
                                ->title('Tidak dapat menghapus item')
                                ->body("Item '{$record->name}' masih terkait dengan {$relatedCount} purchase order. Hapus purchase order terkait terlebih dahulu.")
                                ->danger()
                                ->duration(8000)
                                ->send();

                            // Batalkan action
                            $action->cancel();
                        }
                    })
                    ->action(function (WarehouseItem $record) {
                        try {
                            $record->delete();

                            Notification::make()
                                ->title('Item berhasil dihapus')
                                ->success()
                                ->send();
                        } catch (QueryException $e) {
                            if ($e->getCode() == 23000) {
                                Notification::make()
                                    ->title('Tidak dapat menghapus item')
                                    ->body('Item masih terkait dengan data lain dan tidak dapat dihapus.')
                                    ->danger()
                                    ->duration(8000)
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Terjadi kesalahan')
                                    ->body('Gagal menghapus item. Silakan coba lagi.')
                                    ->danger()
                                    ->send();
                            }
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Custom Bulk Delete dengan pengecekan
                    ExportBulkAction::make(),
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function (Tables\Actions\DeleteBulkAction $action, $records) {
                            $itemsWithRelations = [];

                            foreach ($records as $record) {
                                $relatedCount = DB::table('purchase_order_items')
                                    ->where('item_id', $record->id)
                                    ->count();

                                if ($relatedCount > 0) {
                                    $itemsWithRelations[] = $record->name;
                                }
                            }

                            if (!empty($itemsWithRelations)) {
                                Notification::make()
                                    ->title('Tidak dapat menghapus beberapa item')
                                    ->body('Item berikut masih terkait dengan purchase order: ' . implode(', ', $itemsWithRelations))
                                    ->danger()
                                    ->duration(10000)
                                    ->send();

                                $action->cancel();
                            }
                        }),
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
            'index' => Pages\ListWarehouseItems::route('/'),
            'create' => Pages\CreateWarehouseItem::route('/create'),
            'edit' => Pages\EditWarehouseItem::route('/{record}/edit'),
        ];
    }
}
