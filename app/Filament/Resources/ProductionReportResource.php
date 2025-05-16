<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductionReportResource\Pages;
use App\Filament\Resources\ProductionReportResource\RelationManagers;
use App\Models\DailyMenuItem;
use App\Models\ProductionReport;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;

class ProductionReportResource extends Resource
{
    protected static ?string $model = ProductionReport::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box-arrow-down';

    protected static ?string $navigationGroup = 'Produksi & Pengiriman';


    public static function form(Form $form): Form
    {
        return $form->schema([
            DatePicker::make('date')
                ->label('Tanggal')
                ->required()
                ->reactive()
                ->afterStateUpdated(function ($state, callable $set) {
                    // Reset items when date changes
                    $set('items', []);

                    if (!$state) {
                        return;
                    }

                    // Get daily menu items for the selected date
                    $dailyMenuItems = self::getDailyMenuItemsForDate($state);

                    if ($dailyMenuItems->isEmpty()) {
                        return;
                    }

                    // Prepare items data
                    $items = $dailyMenuItems->map(function ($item) {
                        return [
                            'daily_menu_item_id' => $item->id,
                            'target_qty' => $item->qty,
                            'actual_qty' => 0,
                            'status' => 'kurang',
                        ];
                    })->toArray();

                    $set('items', $items);
                })
                ->columnSpan('full'),

            Repeater::make('items')
                ->label('Item Produksi')
                ->relationship()
                ->schema([
                    Select::make('daily_menu_item_id')
                        ->label('Menu')
                        ->options(function (callable $get) {
                            $date = $get('../../date');
                            if (!$date) {
                                return [];
                            }
                            return self::getDailyMenuItemsForDate($date)->pluck('menu_name', 'id');
                        })
                        ->required()
                        ->reactive(),

                    TextInput::make('target_qty')
                        ->label('Target')
                        ->numeric()
                        ->required(),

                    TextInput::make('actual_qty')
                        ->label('Realisasi')
                        ->numeric()
                        ->required()
                        ->default(0)
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $get, callable $set) {
                            $target = (int)$get('target_qty');
                            $actual = (int)$state;

                            $status = match (true) {
                                $actual == $target => 'tercukupi',   // Ubah dari 'tercukupi' menjadi 'pas'
                                $actual < $target => 'kurang',
                                $actual > $target => 'lebih',
                            };

                            $set('status', $status);
                        }),

                    Select::make('status')
                        ->label('Status')
                        ->required()
                        ->default('kurang')
                        ->options([
                            'kurang' => 'Kurang',
                            'tercukupi' => 'Pas',
                            'lebih' => 'Lebih',
                        ])
                        ->disabled() // Disabled karena diisi otomatis
                        ->reactive()
                        ->dehydrated()
                ])
                ->columns(4)
                ->columnSpan('full')
                ->defaultItems(0), // Start with no items
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')
                    ->label('Tanggal')
                    ->date(),

                TextColumn::make('items_count')
                    ->label('Jumlah Item')
                    ->counts('items'),

                // TextColumn::make('status')
                //     ->label('Status')
                //     ->badge()
                //     ->colors([
                //         'danger' => 'kurang',
                //         'success' => 'pas',
                //         'warning' => 'lebih',
                //     ]),

                TextColumn::make('items_status_summary')
                    ->label('Status')
                    ->getStateUsing(function (ProductionReport $record): string {
                        $statusCounts = $record->items()
                            ->selectRaw('status, COUNT(*) as count')
                            ->groupBy('status')
                            ->pluck('count', 'status')
                            ->toArray();

                        $summary = [];

                        if (isset($statusCounts['kurang']) && $statusCounts['kurang'] > 0) {
                            $summary[] = "{$statusCounts['kurang']} kurang";
                        }

                        if (isset($statusCounts['pas']) && $statusCounts['pas'] > 0) {
                            $summary[] = "{$statusCounts['pas']} pas";
                        }

                        if (isset($statusCounts['lebih']) && $statusCounts['lebih'] > 0) {
                            $summary[] = "{$statusCounts['lebih']} lebih";
                        }

                        return implode(', ', $summary);
                    }),

                TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    protected static function getDailyMenuItemsForDate($date): Collection
    {
        // Convert date string to proper format if needed
        if (is_string($date)) {
            $date = \Carbon\Carbon::parse($date)->format('Y-m-d');
        }

        return DailyMenuItem::query()
            ->whereHas('dailyMenu', function ($query) use ($date) {
                $query->where('date', $date);
            })
            ->get();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductionReports::route('/'),
            'create' => Pages\CreateProductionReport::route('/create'),
            'edit' => Pages\EditProductionReport::route('/{record}/edit'),
        ];
    }
}
