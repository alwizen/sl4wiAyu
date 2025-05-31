<?php

namespace App\Filament\Resources\PurchaseOrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class ReceivingsRelationManager extends RelationManager
{
    protected static string $relationship = 'receivings';

    protected static ?string $title = 'Riwayat Penerimaan Barang';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('received_date')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('received_date')
            ->columns([
                    TextColumn::make('received_date')
                        ->dateTime('d M Y')
                        ->label('Tanggal Terima'),
                
                    TextColumn::make('stockReceivingItems')
                        ->label('Item Diterima')
                        ->formatStateUsing(function ($state, $record) {
                            return $record->stockReceivingItems->map(function ($item) {
                                return $item->warehouseItem?->name . ' (' . $item->received_quantity . ')';
                            })->join(', ');
                        })
                        ->wrap(),
                
                    TextColumn::make('updated_at')
                        ->since()
                        ->label('Terakhir Diperbarui'),
                ])
                
            ->filters([
                //
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    ExportBulkAction::make()
                ]),
            ]);
    }
}
