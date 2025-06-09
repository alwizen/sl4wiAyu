<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductionReportResource\Pages;
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
use Illuminate\Support\Collection;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;


class ProductionReportResource extends Resource
{
    protected static ?string $model = ProductionReport::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box-arrow-down';

    protected static ?string $label = 'Produksi';

    protected static ?string $navigationLabel = 'Produksi';

    protected static ?string $navigationGroup = 'Produksi & Pengiriman';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi Laporan Produksi')
                ->schema([
                    DatePicker::make('production_date')
                        ->label('Tanggal')
                        ->required()
                        ->live()
                        ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, $state) {
                            // Reset items when date changes
                            $set('items', []);
                            $set('daily_menu_id', null);

                            if (!$state) {
                                return;
                            }

                            // Cari DailyMenu berdasarkan tanggal yang dipilih
                            $dailyMenu = \App\Models\DailyMenu::where('menu_date', $state)->first();

                            if ($dailyMenu) {
                                $set('daily_menu_id', $dailyMenu->id);

                                // Siapkan data untuk production report items dari daily menu items
                                $productionItems = $dailyMenu->dailyMenuItems->map(function ($item) {
                                    return [
                                        'daily_menu_item_id' => $item->id,
                                        'target_qty' => $item->target_quantity ?? 0,
                                        'actual_qty' => 0,
                                        'status' => 'kurang',
                                    ];
                                })->toArray();

                                $set('items', $productionItems);
                            }
                        })
                        ->columnSpan('full'),

                    Forms\Components\Hidden::make('daily_menu_id')
                        ->afterStateHydrated(function (Forms\Set $set, Forms\Get $get, $state, ?ProductionReport $record) {
                            // Jika ini adalah edit form dan daily_menu_id belum diisi
                            if ($record && !$state) {
                                // Cari DailyMenu berdasarkan tanggal produksi pada record
                                $dailyMenu = \App\Models\DailyMenu::where('menu_date', $record->production_date)->first();
                                if ($dailyMenu) {
                                    $set('daily_menu_id', $dailyMenu->id);
                                }
                            }
                        }),

                    // Tampilkan informasi menu harian jika ada
                    Forms\Components\Placeholder::make('daily_menu_info')
                        ->label('Informasi Menu Harian')
                        ->content(function (Forms\Get $get) {
                            $dailyMenuId = $get('daily_menu_id');

                            if (!$dailyMenuId) {
                                return 'Tidak ada menu harian untuk tanggal yang dipilih.';
                            }

                            $dailyMenu = \App\Models\DailyMenu::find($dailyMenuId);

                            if (!$dailyMenu) {
                                return 'Menu harian tidak ditemukan.';
                            }

                            $menuItems = $dailyMenu->dailyMenuItems()->count();

                            return "Menu Harian Tanggal {$dailyMenu->menu_date} dengan {$menuItems} item menu";
                        }),
                ]),

            Forms\Components\Section::make('Item Produksi')
                ->schema([
                    Repeater::make('items')
                        ->label('Item Produksi')
                        ->relationship()
                        ->schema([
                            Select::make('daily_menu_item_id')
                                ->label('Menu')
                                ->options(function (Forms\Get $get, ?string $state = null) {
                                    $dailyMenuId = $get('../../daily_menu_id');
                                    
                                    // Jika tidak ada daily_menu_id tapi ada state (ID item yang sudah ada)
                                    if (!$dailyMenuId && $state) {
                                        // Cari daily_menu_item langsung berdasarkan ID
                                        $dailyMenuItem = DailyMenuItem::find($state);
                                        if ($dailyMenuItem) {
                                            // Dapatkan daily_menu_id dan set ke form
                                            $dailyMenuId = $dailyMenuItem->daily_menu_id;
                                        }
                                    }
                                    
                                    if (!$dailyMenuId) {
                                        return [];
                                    }

                                    return \App\Models\DailyMenuItem::query()
                                        ->where('daily_menu_id', $dailyMenuId)
                                        ->with('menu')
                                        ->get()
                                        ->mapWithKeys(function ($item) {
                                            $menuName = $item->menu->menu_name ?? 'Menu Tidak Ditemukan';
                                            $targetGroup = $item->targetGroup->name ?? '';
                                            return [$item->id => "{$menuName} - {$targetGroup}"];
                                        });
                                })
                                ->required()
                                ->disabled() // Disabled karena diisi otomatis
                                ->dehydrated()
                                ->afterStateHydrated(function ($component, $state, Forms\Set $set, $record = null) {
                                    if ($state) {
                                        // Dapatkan informasi daily menu item untuk populating fields lain
                                        $dailyMenuItem = DailyMenuItem::find($state);
                                        if ($dailyMenuItem) {
                                            $set('target_qty', $dailyMenuItem->target_quantity ?? 0);
                                        }
                                    }
                                }),

                            TextInput::make('target_qty')
                                ->label('Target')
                                ->numeric()
                                ->required()
                                ->disabled()
                                ->dehydrated(),

                            TextInput::make('actual_qty')
                                ->label('Realisasi')
                                ->numeric()
                                ->required()
                                ->default(0)
                                ->live()
                                ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                    $target = (int)$get('target_qty');
                                    $actual = (int)$state;

                                    $difference = $actual - $target;

                                    $status = match (true) {
                                        $actual == $target => 'tercukupi',
                                        $actual < $target => 'kurang',
                                        $actual > $target => 'lebih',
                                    };

                                    $set('diference_qty', $difference);
                                    $set('status', $status);
                                }),

                            TextInput::make('diference_qty')
                                ->label('Selisih')
                                ->numeric()
                                ->disabled()
                                ->reactive()
                                ->dehydrated(),

                            Select::make('status')
                                ->label('Status')
                                ->required()
                                ->default('kurang')
                                ->options([
                                    'kurang' => 'Kurang',
                                    'tercukupi' => 'Tercukupi',
                                    'lebih' => 'Lebih',
                                ])
                                ->disabled()
                                ->dehydrated()
                        ])
                        ->columns(5)
                        ->columnSpan('full')
                        ->defaultItems(0)
                ])
                ->columnSpan('full')
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('production_date')
                    ->date()
                    ->searchable()
                    ->sortable()
                    ->label('Tanggal Produksi'),

                Tables\Columns\TextColumn::make('items.dailyMenuItem.menu.menu_name')
                    ->label('Menu')
                    ->searchable()
                    ->listWithLineBreaks(),

                Tables\Columns\TextColumn::make('items.dailyMenuItem.targetGroup.name')
                    ->label('Target Group')
                    ->listWithLineBreaks(),

                Tables\Columns\TextColumn::make('items.target_qty')
                    ->label('Jumlah Target')
                    ->numeric(
                        decimalPlaces: 0,
                        decimalSeparator: ',',
                        thousandsSeparator: '.'
                    )
                    ->suffix(' porsi')
                    ->listWithLineBreaks(),

                Tables\Columns\TextColumn::make('items.actual_qty')
                    ->label('Jumlah Aktual')
                    ->numeric(
                        decimalPlaces: 0,
                        decimalSeparator: ',',
                        thousandsSeparator: '.'
                    )
                    ->suffix(' porsi')
                    ->listWithLineBreaks(),

                Tables\Columns\TextColumn::make('items.status')
                    ->label('Status')
                    ->listWithLineBreaks()
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'kurang' => 'danger',
                        'tercukupi' => 'success',
                        'lebih' => 'warning',
                    }),
                Tables\Columns\TextColumn::make('items.diference_qty')
                    ->label('Selisih')
                    ->suffix(' porsi')
                    ->listWithLineBreaks(),

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
                // Anda dapat menambahkan filter di sini jika diperlukan
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                ExportBulkAction::make(),
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
            ->with(['menu', 'targetGroup']) // Eager load the menu and targetGroup relationships
            ->whereHas('dailyMenu', function ($query) use ($date) {
                $query->where('menu_date', $date);
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