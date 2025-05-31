<?php

namespace App\Filament\Resources\WarehouseItemResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StockIssueItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'stockIssueItems';

    protected static ?string $title = 'Riwayat Pengeluaran Barang';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('issued_quantity')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
{
    return $table
        ->columns([
            TextColumn::make('stockIssue.issue_date')
                ->label('Tanggal')
                ->date('d M Y'),

            TextColumn::make('requested_quantity')
                ->label('Jumlah Keluar'),

            TextColumn::make('stockIssue.description')
                ->label('Keterangan'),

            TextColumn::make('updated_at')
                ->label('Diupdate Pada')
                ->dateTime('d M Y H:i'),
        ])
        ->defaultSort('updated_at', 'desc');
}

}
