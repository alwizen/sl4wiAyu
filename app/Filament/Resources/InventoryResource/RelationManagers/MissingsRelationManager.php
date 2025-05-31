<?php

namespace App\Filament\Resources\InventoryResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class MissingsRelationManager extends RelationManager
{
    protected static string $relationship = 'missings';

    protected static ?string $title = 'Riwayat Kehilangan';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('quantity')
            ->columns([
                Tables\Columns\TextColumn::make('quantity')->label('Jumlah Hilang'),
                Tables\Columns\TextColumn::make('note')->label('Catatan')->wrap(),
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
