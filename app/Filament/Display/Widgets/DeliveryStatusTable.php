<?php

namespace App\Filament\Display\Widgets;

use App\Models\DailyMenu;
use App\Models\Delivery;
use App\Models\ProductionReportItem;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Widgets\TableWidget;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class DeliveryStatusTable extends TableWidget
{
protected static ?string $heading = 'Informasi Pengiriman Hari Ini';

protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $maxHeight = '400px';

    // protected static ?string $pollingInterval = '5s';

    // protected static ?bool $showLoadingIndicator = true;

    public function table(Table $table): Table
    {
        return $table
            ->paginated(false)
            ->poll('5s')
            // ->paginationPageOptions('false')
            ->striped()
            ->query(
                Delivery::query()
                    ->where('delivery_date', Carbon::today())
                    ->with('recipient')
            )
            ->columns([
                Tables\Columns\TextColumn::make('No')
                ->rowIndex(),
                Tables\Columns\TextColumn::make('recipient.name')
                    ->label('Penerima Manfaat'),

                Tables\Columns\TextColumn::make('qty')
                    ->label('Jml')
                    ->suffix(' Box'),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status Pengiriman')
                    ->colors([
                        'primary' => 'dikemas',
                        'warning' => 'dalam_perjalanan',
                        'success' => 'terkirim',
                        'info' => 'selesai',
                        'success' => 'kembali',
                        
                        // 'dikemas' => 'secondary',
                        // 'dalam_perjalanan' => 'gray',
                        // 'terkirim' => 'warning',
                        // 'selesai' => 'info',
                        // 'kembali' => 'success',
                    ]),
                    Tables\Columns\TextColumn::make('received_qty')
                    ->label('Jml. Diterima')
                    ->suffix(' Box'),
                    Tables\Columns\TextColumn::make('prepared_at')
                    ->label('Dikemas')
                    ->dateTime('d/m/Y H:i'),
                    Tables\Columns\TextColumn::make('shipped_at')
                    ->label('Perjalanan')
                    ->dateTime('d/m/Y H:i'),
                    Tables\Columns\TextColumn::make('received_at')
                    ->label('Terkirim')
                    ->dateTime('d/m/Y H:i'),
                    Tables\Columns\TextColumn::make('returned_at')
                    ->label('Kembali')
                    ->dateTime('d/m/Y H:i'), 
                    // ->formatStateUsing(fn ($state) => ucfirst(str_replace('_', ' ', $state))),
                    // Tables\Columns\TextColumn::make('updated_at')
                    // ->label('Terakhir Diperbarui'),
                    // ->label('Qty'),
            ]);
    }
}