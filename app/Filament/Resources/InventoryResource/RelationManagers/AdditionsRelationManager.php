<?php

namespace App\Filament\Resources\InventoryResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class AdditionsRelationManager extends RelationManager
{
    protected static string $relationship = 'additions';

    protected static ?string $title = 'Riwayat Penambahan';


    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('quantity')
            ->columns([
                Tables\Columns\TextColumn::make('quantity')->label('Jumlah'),
                Tables\Columns\TextColumn::make('created_at')->label('Tanggal')->dateTime(),
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
