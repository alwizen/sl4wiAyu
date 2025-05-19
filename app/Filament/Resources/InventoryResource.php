<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InventoryResource\Pages;
use App\Filament\Resources\InventoryResource\RelationManagers;
use App\Models\Inventory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InventoryResource extends Resource
{
    protected static ?string $model = Inventory::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationGroup = 'Gudang';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->required()
                    ->maxLength(255),
                Forms\Components\DatePicker::make('purchase_date')
                    ->required(),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('stock_init')
                    ->required()
                    ->numeric()
                    ->label('Initial Stock'),
                Forms\Components\TextInput::make('addition')
                    ->numeric()
                    ->disabled()
                    ->default(0)
                    ->label('Addition'),
                Forms\Components\TextInput::make('damaged')
                    ->numeric()
                    ->default(0)
                    ->label('Damaged'),
                Forms\Components\TextInput::make('missing')
                    ->numeric()
                    ->default(0)
                    ->label('Missing'),
                // Stock end is calculated, so we don't need it in the form
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('purchase_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('stock_init')
                    ->numeric()
                    ->label('Initial Stock')
                    ->sortable(),
                Tables\Columns\TextColumn::make('addition')
                    ->numeric()
                    ->label('Addition')
                    ->sortable(),
                Tables\Columns\TextColumn::make('damaged')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('missing')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock_end')
                    ->numeric()
                    ->label('End Stock')
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
                Tables\Actions\Action::make('inputAddition')
                    ->label('Input Tambahan')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->color('success')
                    ->form([
                        Forms\Components\TextInput::make('addition_value')
                            ->label('Jumlah Tambahan')
                            ->numeric()
                            ->required()
                            ->helperText('Masukkan jumlah tambahan stok')
                    ])
                    ->action(function (Inventory $record, array $data) {
                        // Menambahkan nilai addition yang baru ke nilai yang sudah ada
                        $record->addition = $record->addition + $data['addition_value'];

                        // Menghitung ulang stock_end
                        $record->stock_end = $record->stock_init + $record->addition - $record->damaged - $record->missing;

                        $record->save();

                        \Filament\Notifications\Notification::make()
                            ->title('Tambahan stok berhasil disimpan')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                ExportBulkAction::make(),
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageInventories::route('/'),
        ];
    }
}
