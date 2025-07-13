<?php

namespace App\Filament\Resources\WarehouseItemResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class StockReceivingItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'stockReceivingItems';

    protected static ?string $title = '';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('received_quantity')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(50)
            ->columns([
                TextColumn::make('stockReceiving.received_date')
                    ->label('Tanggal')
                    ->date('d M Y'),

                TextColumn::make('stockReceiving.purchaseOrder.order_number')
                    ->searchable()
                    ->label('No. PO'),

                TextColumn::make('received_quantity')
                    ->label('Jumlah Masuk')
                    ->summarize(Sum::make()
                        ->label('Total')),

                TextColumn::make('updated_at')
                    ->label('Diupdate')
                    ->dateTime('d M Y H:i'),
            ])
            ->bulkActions([
                ExportBulkAction::make('export')
                    ->label('Ekspor Data')
                    ->icon('heroicon-o-arrow-down-tray'),
            ])
            ->filters([
                Filter::make('received_date_range')
                    ->form([
                        Forms\Components\DatePicker::make('received_date_from')->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('received_date_until')->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query->whereHas('stockReceiving', function (Builder $q) use ($data) {
                            if (!empty($data['received_date_from'])) {
                                $q->whereDate('received_date', '>=', $data['received_date_from']);
                            }
                            if (!empty($data['received_date_until'])) {
                                $q->whereDate('received_date', '<=', $data['received_date_until']);
                            }
                        });
                    }),
            ])
            ->defaultSort('updated_at', 'desc');
    }
}
