<?php

namespace App\Filament\Resources;

use App\Exports\NutritionPlanItemsExport;
use App\Filament\Exports\NutritionPlanExporter;
use App\Filament\Exports\NutritionPlanItemExporter;
use App\Filament\Resources\NutritionPlanResource\Pages;
use App\Models\NutritionPlan;
use App\Models\DailyMenu;
use App\Models\Menu;
use App\Models\TargetGroup;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Hidden;
use Filament\Support\Enums\ActionSize;
use Filament\Tables\Actions\ExportBulkAction;
use Filament\Tables\Columns\Summarizers\Sum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Maatwebsite\Excel\Facades\Excel;
// Import untuk Excel Export
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction as FilamentExcelExportBulkAction;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use Icetalker\FilamentTableRepeater\Forms\Components\TableRepeater;


class NutritionPlanResource extends Resource
{
    protected static ?string $model = NutritionPlan::class;

    protected static ?string $navigationIcon = 'heroicon-o-heart';

    protected static ?string $navigationLabel = 'Rencana Nutrisi';

    protected static ?string $modelLabel = 'Rencana Nutrisi';

    protected static ?string $pluralModelLabel = 'Perencanaan Nutrisi';

    protected static ?string $navigationGroup = 'Ahli Gizi';

    protected static ?int $navigationSort = 2;

    // Form tetap sama...
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Rencana Nutrisi')
                    ->schema([
                        DatePicker::make('nutrition_plan_date')
                            ->label('Tanggal Rencana Nutrisi')
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                // Cari DailyMenu berdasarkan tanggal yang dipilih
                                $dailyMenu = DailyMenu::where('menu_date', $state)->first();

                                if ($dailyMenu) {
                                    $set('daily_menu_id', $dailyMenu->id);

                                    // Siapkan data untuk nutrition plan items dari daily menu items
                                    $nutritionPlanItems = $dailyMenu->dailyMenuItems->map(function ($item) {
                                        return [
                                            'menu_id' => $item->menu_id,
                                            'target_group_id' => $item->target_group_id,
                                            'energy' => 0,
                                            'protein' => 0,
                                            'fat' => 0,
                                            'carb' => 0,
                                            'serat' => 0,
                                            'mineral' => 0,
                                        ];
                                    })->toArray();

                                    $set('nutrition_plan_items', $nutritionPlanItems);
                                } else {
                                    $set('daily_menu_id', null);
                                }
                            }),

                        Hidden::make('daily_menu_id'),

                        // Tampilkan informasi menu harian jika ada
                        Forms\Components\Placeholder::make('daily_menu_info')
                            ->label('Informasi Menu Harian')
                            ->content(function (Get $get) {
                                $dailyMenuId = $get('daily_menu_id');

                                if (!$dailyMenuId) {
                                    return 'Tidak ada menu harian untuk tanggal yang dipilih.';
                                }

                                $dailyMenu = DailyMenu::find($dailyMenuId);

                                if (!$dailyMenu) {
                                    return 'Menu harian tidak ditemukan.';
                                }

                                $menuName = $dailyMenu->menu ? $dailyMenu->menu->menu_name : 'Tidak ada nama menu';

                                return "Menu Harian: {$menuName}";
                            }),
                    ]),

                Section::make('Rencana Nutrisi')
                    ->schema([
                        TableRepeater::make('nutrition_plan_items')
                            ->label('Item Rencana Nutrisi')
                            ->relationship('nutritionPlanItems')
                            ->schema([
                                Select::make('menu_id')
                                    ->label('Menu')
                                    ->options(Menu::pluck('menu_name', 'id'))
                                    ->required()
                                    ->disabled()
                                    ->extraAttributes(['style' => 'width: 130px'])
                                    ->dehydrated(),

                                Select::make('target_group_id')
                                    ->label('Penerima')
                                    ->options(TargetGroup::pluck('name', 'id'))
                                    ->required()
                                    ->disabled()
                                    ->extraAttributes(['style' => 'width: 200px'])
                                    ->dehydrated(),

                                TextInput::make('netto')
                                    ->label('Netto "gr"')
                                    ->numeric()
                                    ->default(0)
                                    ->extraAttributes(['style' => 'width: 90px'])
                                    // ->suffix('gr')
                                    ->required(),

                                TextInput::make('energy')
                                    ->label('EN "kkal"')
                                    ->extraAttributes(['style' => 'width: 80px'])
                                    ->numeric()
                                    // ->suffix('kkal')
                                    ->required(),

                                TextInput::make('protein')
                                    ->label('PRO "gr')
                                    ->extraAttributes(['style' => 'width: 80px'])

                                    ->numeric()
                                    // ->suffix('gr')
                                    ->required(),

                                TextInput::make('fat')
                                    ->label('FAT "gr"')
                                    ->extraAttributes(['style' => 'width: 80px'])
                                    ->numeric()
                                    // ->suffix('gr')
                                    ->required(),

                                TextInput::make('carb')
                                    ->label('KH "gr"')
                                    ->numeric()
                                    ->extraAttributes(['style' => 'width: 80px'])
                                    ->required(),

                                TextInput::make('serat')
                                    ->label('SP "gr"')
                                    ->extraAttributes(['style' => 'width: 80px'])
                                    ->numeric()
                                    // ->suffix('gr')
                                    ->required(),

                            ])
                            ->columns(8),
                    ]),
            ]);
    }

    public static function table(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        return $table
            ->defaultPaginationPageOption(50)
            ->groups([
                \Filament\Tables\Grouping\Group::make('nutrition_plan_date')
                    ->label('Tanggal')
                    ->date('d F Y')
                    ->collapsible()
                    ->orderQueryUsing(fn(\Illuminate\Database\Eloquent\Builder $query, string $direction) => $query->orderBy('nutrition_plan_date', $direction)),
            ])
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('nutrition_plan_date')
                    ->label('Tanggal')
                    ->date('d F Y')
                    ->sortable()
                    ->searchable(),

                \Filament\Tables\Columns\TextColumn::make('nutritionPlanItems.menu.menu_name')
                    ->label('Menu')
                    ->listWithLineBreaks()
                    ->searchable(),

                \Filament\Tables\Columns\TextColumn::make('nutritionPlanItems.targetGroup.name')
                    ->label('Penerima')
                    ->listWithLineBreaks()
                    ->searchable(),

                \Filament\Tables\Columns\TextColumn::make('nutritionPlanItems.energy')
                    ->label('Energi (kkal)')
                    ->listWithLineBreaks()
                    // ->summarize([
                    //     \Filament\Tables\Columns\Summarizers\Sum::make()
                    //         ->formatStateUsing(fn(string $state): string => number_format((float)$state, 2, ',', '.') . " kkal")
                    // ])
                    ->formatStateUsing(fn(string $state): string => number_format((float)$state, 2, ',', '.') . " kkal"),

                \Filament\Tables\Columns\TextColumn::make('nutritionPlanItems.protein')
                    ->label('Protein (gr)')
                    ->listWithLineBreaks()
                    // ->summarize([
                    //     \Filament\Tables\Columns\Summarizers\Sum::make()
                    //         ->formatStateUsing(fn(string $state): string => number_format((float)$state, 2, ',', '.') . " gr")
                    // ])
                    ->formatStateUsing(fn(string $state): string => number_format((float)$state, 2, ',', '.') . " gr"),

                \Filament\Tables\Columns\TextColumn::make('nutritionPlanItems.fat')
                    ->label('Lemak (gr)')
                    ->listWithLineBreaks()
                    // ->summarize([
                    //     \Filament\Tables\Columns\Summarizers\Sum::make()
                    //         ->formatStateUsing(fn(string $state): string => number_format((float)$state, 2, ',', '.') . " gr")
                    // ])
                    ->formatStateUsing(fn(string $state): string => number_format((float)$state, 2, ',', '.') . " gr"),

                \Filament\Tables\Columns\TextColumn::make('nutritionPlanItems.carb')
                    ->label('Karbo (gr)')
                    ->listWithLineBreaks()
                    // ->summarize([
                    //     \Filament\Tables\Columns\Summarizers\Sum::make()
                    //         ->formatStateUsing(fn(string $state): string => number_format((float)$state, 2, ',', '.') . " gr")
                    // ])
                    ->formatStateUsing(fn(string $state): string => number_format((float)$state, 2, ',', '.') . " gr"),

                \Filament\Tables\Columns\TextColumn::make('nutritionPlanItems.serat')
                    ->label('Serat (gr)')
                    ->listWithLineBreaks()
                    // ->summarize([
                    //     \Filament\Tables\Columns\Summarizers\Sum::make()
                    //         ->formatStateUsing(fn(string $state): string => number_format((float)$state, 2, ',', '.') . " gr")
                    // ])
                    ->formatStateUsing(fn(string $state): string => number_format((float)$state, 2, ',', '.') . " gr"),

                \Filament\Tables\Columns\TextColumn::make('nutritionPlanItems.netto')
                    ->label('Netto (gr)')
                    ->listWithLineBreaks()
                    // ->summarize([
                    //     \Filament\Tables\Columns\Summarizers\Sum::make()
                    //         ->formatStateUsing(fn(string $state): string => number_format((float)$state, 2, ',', '.') . " gr")
                    // ])
                    ->formatStateUsing(fn(string $state): string => number_format((float)$state, 2, ',', '.') . " gr"),

                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime('d F Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                \Filament\Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui Pada')
                    ->dateTime('d F Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                \Filament\Tables\Filters\Filter::make('nutrition_plan_date')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('dari_tanggal')
                            ->label('Dari Tanggal'),
                        \Filament\Forms\Components\DatePicker::make('sampai_tanggal')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (\Illuminate\Database\Eloquent\Builder $query, array $data): \Illuminate\Database\Eloquent\Builder {
                        return $query
                            ->when(
                                $data['dari_tanggal'],
                                fn(\Illuminate\Database\Eloquent\Builder $query, $date): \Illuminate\Database\Eloquent\Builder => $query->whereDate('nutrition_plan_date', '>=', $date),
                            )
                            ->when(
                                $data['sampai_tanggal'],
                                fn(\Illuminate\Database\Eloquent\Builder $query, $date): \Illuminate\Database\Eloquent\Builder => $query->whereDate('nutrition_plan_date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                ActionGroup::make([
                    \Filament\Tables\Actions\Action::make('print')
                        ->label('Cetak PDF')
                        ->icon('heroicon-o-printer')
                        ->url(fn(NutritionPlan $record) => route('nutrition-plans.print', $record))
                        ->openUrlInNewTab(),
                    \Filament\Tables\Actions\EditAction::make(),
                    \Filament\Tables\Actions\DeleteAction::make(),
                ])
                    ->button()
                    ->label('Aksi')
                    ->icon('heroicon-o-paper-clip')
                    ->color('warning')
                    ->size(ActionSize::Small)
            ])
            // ->headerActions([
            //     Action::make('Ekspor Semua Item Rencana Nutrisi')
            //         ->icon('heroicon-o-arrow-down-tray')
            //         ->color('success')
            //         ->action(function () {
            //             return Excel::download(new NutritionPlanItemsExport, 'nutrition-plan-items.xlsx');
            //         }),
            // ])
            ->bulkActions([
                \Filament\Tables\Actions\BulkActionGroup::make([
                    \Filament\Tables\Actions\DeleteBulkAction::make(),

                    BulkAction::make('export-selected')
                        ->label('Ekspor Item Nutrisi')
                        ->icon('heroicon-o-document-arrow-down')
                        ->action(function (Collection $records) {
                            $ids = $records->pluck('id');

                            $timestamp = Carbon::now()->format('Ymd_His'); // Format: 20250530_143210

                            return Excel::download(
                                new NutritionPlanItemsExport($ids),
                                "selected-nutrition-plan-items_{$timestamp}.xlsx"
                            );
                        }),
                ]),
            ])
            ->defaultSort('nutrition_plan_date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNutritionPlans::route('/'),
            'create' => Pages\CreateNutritionPlan::route('/create'),
            'edit' => Pages\EditNutritionPlan::route('/{record}/edit'),
        ];
    }
}
