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
                                    
                                    // Siapkan data untuk nutrition plan items dari daily menu items
                                    $nutritionPlanItems = $dailyMenu->dailyMenuItems->map(function ($item) {
                                        return [
                                            'menu_id' => $item->menu_id,
                                            'target_group_id' => $item->target_group_id,
                                            'energy' => 0,
                                            'protein' => 0,
                                            'fat' => 0,
                                            'carb' => 0,
                                            'vitamin' => 0,
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
                        Repeater::make('nutrition_plan_items')
                            ->label('Item Rencana Nutrisi')
                            ->relationship('nutritionPlanItems')
                            ->schema([
                                Select::make('menu_id')
                                    ->label('Menu')
                                    ->options(Menu::pluck('menu_name', 'id'))
                                    ->required()
                                    ->disabled()
                                    ->dehydrated(),
                                
                                Select::make('target_group_id')
                                    ->label('Penerima Manfaat')
                                    ->options(TargetGroup::pluck('name', 'id'))
                                    ->required()
                                    ->disabled()
                                    ->dehydrated(),

                                    TextInput::make('netto')
                                    ->label('Netto')
                                    ->numeric()
                                    ->suffix('gr')
                                    ->required(),
                                
                                TextInput::make('energy')
                                    ->label('Energi')
                                    ->numeric()
                                    ->suffix('kkal')
                                    ->required(),
                                
                                TextInput::make('protein')
                                    ->label('Protein')
                                    ->numeric()
                                    ->suffix('gr')
                                    ->required(),
                                
                                TextInput::make('fat')
                                    ->label('Lemak')
                                    ->numeric()
                                    ->suffix('gr')
                                    ->required(),
                                
                                TextInput::make('carb')
                                    ->label('Karbohidrat')
                                    ->numeric()
                                    ->required()
                                    ->suffix('gr'),
                                
                                // TextInput::make('vitamin')
                                //     ->label('Vitamin')
                                //     ->numeric()
                                //     ->required(),
                                
                            ])
                            ->columns(7),
                    ]),
            ]);
    }
    public static function table(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('nutrition_plan_date')
                    ->label('Tanggal Rencana Nutrisi')
                    ->date('d F Y')
                    ->sortable()
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('dailyMenu.menu.menu_name')
                    ->label('Menu Harian')
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('nutritionPlanItems.count')
                    ->label('Jumlah Item')
                    ->counts('nutritionPlanItems')
                    ->sortable(),
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
                                fn (\Illuminate\Database\Eloquent\Builder $query, $date): \Illuminate\Database\Eloquent\Builder => $query->whereDate('nutrition_plan_date', '>=', $date),
                            )
                            ->when(
                                $data['sampai_tanggal'],
                                fn (\Illuminate\Database\Eloquent\Builder $query, $date): \Illuminate\Database\Eloquent\Builder => $query->whereDate('nutrition_plan_date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                \Filament\Tables\Actions\EditAction::make(),
                \Filament\Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Tables\Actions\BulkActionGroup::make([
                    \Filament\Tables\Actions\DeleteBulkAction::make(),
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