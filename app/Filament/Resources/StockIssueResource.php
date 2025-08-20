<?php

namespace App\Filament\Resources;

use App\Exports\StockIssueItemsExport;
use App\Filament\Resources\StockIssueResource\Pages;
use App\Models\StockIssue;
use App\Models\WarehouseItem;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section as InfoSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Icetalker\FilamentTableRepeater\Forms\Components\TableRepeater;

class StockIssueResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = StockIssue::class;

    protected static ?string $navigationIcon  = 'heroicon-o-arrow-up-on-square';
    protected static ?string $navigationGroup = 'Pengadaan & Permintaan';
    protected static ?string $label           = 'Permintaan Bahan Masak';
    protected static ?string $navigationLabel = 'Permintaan Bahan Masak';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Card::make()
                ->columns(2)
                ->schema([
                    DatePicker::make('issue_date')
                        ->label('Tanggal Permintaan')
                        ->default(today())
                        ->required(),

                    Select::make('status')
                        ->label('Status')
                        ->options([
                            'Draft'     => 'Diminta',
                            'Submitted' => 'Disiapkan',
                        ])
                        ->default('Draft')
                        ->disabled(), // status diubah oleh gudang lewat action
                ]),

            Card::make()
                ->schema([
                    TableRepeater::make('items')
                        ->label('Daftar Item yang Diminta')
                        ->relationship()
                        ->schema([
                            // Pakai relationship agar ringan & searchable
                            Select::make('warehouse_item_id')
                                ->label('Item Gudang')
                                ->relationship('warehouseItem', 'name')
                                ->searchable()
                                ->preload()
                                ->required(),

                            TextInput::make('requested_quantity')
                                ->label('Jumlah Diminta')
                                ->numeric()
                                ->step('0.01')   // ← 2 desimal
                                ->minValue(0.01)
                                ->required(),
                        ])
                        ->columns(2),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('issue_date')
                    ->label('Tanggal Permintaan')
                    ->date('d-m-Y')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn($state) => match ($state) {
                        'Draft'     => 'Diminta',
                        'Submitted' => 'Disiapkan',
                        default     => $state,
                    })
                    ->colors([
                        'warning' => 'Draft',
                        'success' => 'Submitted',
                    ])
                    ->sortable()
                    ->searchable(),

                TextColumn::make('items_count')
                    ->label('Jumlah Item')
                    ->counts('items'),

                TextColumn::make('updated_at')
                    ->label('Terakhir Diperbarui')
                    ->date('d-m-Y H:i'),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('Lihat Detail')
                    ->icon('heroicon-o-eye')
                    ->slideOver()
                    ->infolist([
                        \Filament\Infolists\Components\Section::make('Informasi Umum')
                            ->schema([
                                \Filament\Infolists\Components\TextEntry::make('issue_date')
                                    ->label('Tanggal Permintaan')
                                    ->date('d-m-Y'),

                                // ❌ sebelumnya: ->state(fn($r) => ...)
                                // ✅ gunakan $state atau $record
                                \Filament\Infolists\Components\TextEntry::make('status')
                                    ->label('Status')
                                    ->formatStateUsing(fn($state) => $state === 'Draft' ? 'Diminta' : 'Disiapkan'),
                            ])
                            ->columns(2),

                        \Filament\Infolists\Components\Section::make('Daftar Item')
                            ->schema([
                                \Filament\Infolists\Components\RepeatableEntry::make('items')
                                    ->label('Item Diminta')
                                    ->schema([
                                        \Filament\Infolists\Components\TextEntry::make('warehouseItem.name')
                                            ->label('Nama Barang'),

                                        // ❌ jangan pakai ->state(fn($item) ...) dengan $item
                                        // ✅ pakai $state untuk nilai field ini
                                        \Filament\Infolists\Components\TextEntry::make('requested_quantity')
                                            ->label('Diminta')
                                            ->formatStateUsing(fn($state) => number_format((float) $state, 2, ',', '.')),

                                        \Filament\Infolists\Components\TextEntry::make('issued_quantity')
                                            ->label('Dikeluarkan')
                                            ->formatStateUsing(
                                                fn($state) =>
                                                $state !== null
                                                    ? number_format((float) $state, 2, ',', '.')
                                                    : '-'
                                            ),
                                    ])
                                    ->columns(3),
                            ]),
                    ]),

                Tables\Actions\Action::make('mark_prepared')
                    ->label('Tandai Disiapkan')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->visible(
                        fn($record) =>
                        $record->status === 'Draft' &&
                            auth()->user()?->can('mark_prepared_stock::issue')
                    )
                    ->requiresConfirmation()
                    ->action(function (StockIssue $record) {
                        try {
                            DB::transaction(function () use ($record) {
                                // ambil item + relasi untuk unit/nama
                                $items = $record->items()->with('warehouseItem')->get();

                                foreach ($items as $line) {
                                    $qty = (float) $line->requested_quantity;
                                    if ($qty <= 0) {
                                        continue;
                                    }

                                    // kunci baris stok supaya aman dari race condition
                                    $item = WarehouseItem::whereKey($line->warehouse_item_id)
                                        ->lockForUpdate()
                                        ->first();

                                    if (! $item) {
                                        throw new \RuntimeException('Item gudang tidak ditemukan.');
                                    }

                                    $current = (float) $item->stock;
                                    if ($current < $qty) {
                                        throw new \RuntimeException(
                                            "Stok {$item->name} tidak mencukupi. Tersedia: " .
                                                number_format($current, 2, ',', '.')
                                        );
                                    }

                                    // kurangi stok & simpan 2 desimal
                                    $item->stock = round($current - $qty, 2);
                                    $item->save();

                                    // catat issued_quantity = yang benar-benar dikeluarkan
                                    $line->issued_quantity = round($qty, 2);
                                    $line->save();
                                }

                                // update status dokumen
                                $record->update(['status' => 'Submitted']);
                            });

                            Notification::make()
                                ->title('Permintaan disiapkan')
                                ->body('Stok berhasil dikurangi sesuai jumlah diminta.')
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Gagal menyiapkan permintaan')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Tables\Actions\EditAction::make()->label('Ubah'),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('export-selected')
                        ->label('Ekspor Permintaan Barang')
                        ->icon('heroicon-o-document-arrow-down')
                        ->action(function (Collection $records) {
                            $ids = $records->pluck('id');
                            $timestamp = Carbon::now()->format('Ymd_His');

                            return \Maatwebsite\Excel\Facades\Excel::download(
                                new \App\Exports\StockIssueItemsExport($ids),
                                "stock-issues_{$timestamp}.xlsx"
                            );
                        }),
                ]),
            ]);
    }

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
            'mark_prepared',
        ];
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListStockIssues::route('/'),
            'create' => Pages\CreateStockIssue::route('/create'),
            'edit'   => Pages\EditStockIssue::route('/{record}/edit'),
        ];
    }
}
