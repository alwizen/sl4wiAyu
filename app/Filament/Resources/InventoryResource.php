<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InventoryResource\Pages;
use App\Filament\Resources\InventoryResource\RelationManagers;
use App\Filament\Resources\InventoryResource\RelationManagers\AdditionsRelationManager;
use App\Filament\Resources\InventoryResource\RelationManagers\MissingsRelationManager;
use App\Models\Inventory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\ActionGroup;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class InventoryResource extends Resource
{
    protected static ?string $model = Inventory::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationGroup = 'Gudang';

    protected static ?string $navigationLabel = 'Inventaris';

    protected static ?string $label = 'Inventaris';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            // 'Name' => $record->name,
            'Stok' => $record->stock_end,
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->label('Kode Barang')
                    ->default(fn() => 'INV-' . str_pad(random_int(0, 99999), 5, '0', STR_PAD_LEFT))
                    ->disabled()
                    ->dehydrated()
                    ->required(),
                Forms\Components\DatePicker::make('purchase_date')
                    ->label('Tanggal Pembelian')
                    ->required(),
                Forms\Components\TextInput::make('name')
                    ->label('Nama Barang')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('stock_init')
                    ->label('Jumlah Awal')
                    ->required()
                    ->numeric()
                    ->label('Initial Stock'),
                Forms\Components\TextInput::make('addition')
                    ->label('Tambahan')
                    ->numeric()
                    ->disabled()
                    ->default(0)
                    ->label('Addition'),
                Forms\Components\TextInput::make('damaged')
                    ->label('Rusak')
                    ->numeric()
                    ->disabled()
                    ->default(0)
                    ->label('Damaged'),
                Forms\Components\TextInput::make('missing')
                    ->label('Hilang')
                    ->numeric()
                    ->disabled()
                    ->default(0)
                    ->label('Missing'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Kode Barang')
                    ->searchable(),
                Tables\Columns\TextColumn::make('purchase_date')
                    ->label('Tanggal Pembelian')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Barang')
                    ->searchable(),
                Tables\Columns\TextColumn::make('stock_init')
                    ->label('Jumlah Awal')
                    ->sortable(),
                Tables\Columns\TextColumn::make('addition')
                    ->label('Tambahan')
                    ->badge()
                    ->color('success')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('damaged')
                    ->label('Rusak')
                    ->badge()
                    ->color('warning')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('missing')
                    ->label('Hilang')
                    ->badge()
                    ->color('danger')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock_end')
                    ->numeric()
                    ->label('Jumlah Akhir')
                    ->sortable()
                    ->formatStateUsing(function (Inventory $record) {
                        // Calculate stock_end
                        return $record->stock_init + $record->addition - $record->damaged - $record->missing;
                    }),
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
                //
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\Action::make('inputAddition')
                        ->label('Input Tambahan')
                        ->icon('heroicon-o-clipboard-document-check')
                        ->color('success')
                        ->form([
                            Forms\Components\TextInput::make('addition_value')
                                ->label('Jumlah Tambahan')
                                ->numeric()
                                ->required()
                                ->helperText('Masukkan jumlah tambahan stok'),
                            Forms\Components\Textarea::make('note')
                                ->label('Catatan')
                                ->placeholder('Keterangan tambahan stok...')
                        ])
                        ->action(function (Inventory $record, array $data) {
                            $record->addition += $data['addition_value'];
                            $record->stock_end = $record->stock_init + $record->addition - $record->damaged - $record->missing;
                            $record->save();

                            \App\Models\InventoryAddition::create([
                                'inventory_id' => $record->id,
                                'quantity' => $data['addition_value'],
                                'note' => null,
                            ]);

                            \Filament\Notifications\Notification::make()
                                ->title('Tambahan stok berhasil disimpan')
                                ->success()
                                ->send();
                        }),


                    Tables\Actions\Action::make('inputMissing')
                        ->label('Input Kehilangan')
                        ->icon('heroicon-o-exclamation-circle')
                        ->color('danger')
                        ->form([
                            Forms\Components\TextInput::make('missing_value')
                                ->label('Jumlah Hilang')
                                ->numeric()
                                ->required()
                                ->helperText('Masukkan jumlah kehilangan barang'),
                            Forms\Components\Textarea::make('note')
                                ->label('Catatan')
                                ->rows(2)
                                ->placeholder('Keterangan kehilangan...')
                        ])
                        ->action(function (Inventory $record, array $data) {
                            $record->missing += $data['missing_value'];
                            $record->stock_end = $record->stock_init + $record->addition - $record->damaged - $record->missing;
                            $record->save();

                            \App\Models\InventoryMissing::create([
                                'inventory_id' => $record->id,
                                'quantity' => $data['missing_value'],
                                'note' => $data['note'],
                            ]);

                            \Filament\Notifications\Notification::make()
                                ->title('Data kehilangan berhasil dicatat')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make()
                        ->label('Edit / Riwayat'),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])

            ->bulkActions([

                Tables\Actions\BulkActionGroup::make([
                    ExportBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            AdditionsRelationManager::class,
            MissingsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInventories::route('/'),
            'create' => Pages\CreateInventory::route('/create'),
            'edit' => Pages\EditInventory::route('/{record}/edit'),
        ];
    }
}
