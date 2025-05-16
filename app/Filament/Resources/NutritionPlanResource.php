<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NutritionPlanResource\Pages;
use App\Models\NutritionPlan;
use App\Models\DailyMenu;
use App\Models\Menu;
use App\Models\TargetGroup;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Filament\Forms\Get;
use Filament\Forms\Set;

class NutritionPlanResource extends Resource
{
    protected static ?string $model = NutritionPlan::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Rencana Nutrisi';

    protected static ?string $modelLabel = 'Rencana Nutrisi';

    protected static ?string $pluralModelLabel = 'Rencana Nutrisi';

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

                Section::make('Rincian Menu Harian')
                    ->schema([
                        Repeater::make('daily_menu_items')
                            ->label('Item Menu Harian')
                            ->schema([
                                Select::make('menu_id')
                                    ->label('Menu')
                                    ->options(Menu::pluck('menu_name', 'id'))
                                    ->disabled()
                                    ->dehydrated(false),

                                Select::make('target_group_id')
                                    ->label('Kelompok Target')
                                    ->options(TargetGroup::pluck('name', 'id'))
                                    ->disabled()
                                    ->dehydrated(false),

                                TextInput::make('target_quantity')
                                    ->label('Kuantitas Target')
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated(false),
                            ])
                            ->itemLabel(fn (array $state): ?string => $state['menu_id'] ? Menu::find($state['menu_id'])?->menu_name : null)
                            ->disabled()
                            ->visible(fn (Get $get): bool => (bool) $get('daily_menu_id'))
                            ->afterStateHydrated(function (Repeater $component, Get $get, Set $set) {
                                $dailyMenuId = $get('daily_menu_id');
                                if (!$dailyMenuId) {
                                    return;
                                }

                                $dailyMenu = DailyMenu::find($dailyMenuId);
                                if (!$dailyMenu) {
                                    return;
                                }

                                $items = $dailyMenu->dailyMenuItems->map(function ($item) {
                                    return [
                                        'menu_id' => $item->menu_id,
                                        'target_group_id' => $item->target_group_id,
                                        'target_quantity' => $item->target_quantity,
                                    ];
                                })->toArray();

                                $set('daily_menu_items', $items);
                            }),
                    ])
                    ->visible(fn (Get $get): bool => (bool) $get('daily_menu_id')),

                Section::make('Rencana Nutrisi')
                    ->schema([
                        Repeater::make('nutrition_plan_items')
                            ->label('Item Rencana Nutrisi')
                            ->relationship('nutritionPlanItems')
                            ->schema([
                                Select::make('menu_id')
                                    ->label('Menu')
                                    ->options(Menu::pluck('menu_name', 'id'))
                                    ->required(),

                                Select::make('target_group_id')
                                    ->label('Kelompok Target')
                                    ->options(TargetGroup::pluck('name', 'id'))
                                    ->required(),

                                TextInput::make('energy')
                                    ->label('Energi')
                                    ->numeric()
                                    ->required(),

                                TextInput::make('protein')
                                    ->label('Protein')
                                    ->numeric()
                                    ->required(),

                                TextInput::make('fat')
                                    ->label('Lemak')
                                    ->numeric()
                                    ->required(),

                                TextInput::make('carb')
                                    ->label('Karbohidrat')
                                    ->numeric()
                                    ->required(),

                                TextInput::make('vitamin')
                                    ->label('Vitamin')
                                    ->numeric()
                                    ->required(),

                                TextInput::make('mineral')
                                    ->label('Mineral')
                                    ->numeric()
                                    ->required(),
                            ])
                            ->columnSpanFull(),
                    ]),
            ]);
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
