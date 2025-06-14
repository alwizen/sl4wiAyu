<?php

namespace App\Filament\Resources;

use App\Exports\DailyMenuItemsExport;
use App\Filament\Resources\DailyMenuResource\Pages;
use App\Filament\Resources\DailyMenuResource\RelationManagers;
use App\Models\DailyMenu;
use App\Models\Menu;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Maatwebsite\Excel\Facades\Excel;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class DailyMenuResource extends Resource
{
    protected static ?string $model = DailyMenu::class;

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationIcon = 'heroicon-o-numbered-list';

    protected static ?string $navigationGroup = 'Ahli Gizi';

    protected static ?string $navigationLabel = 'Menu Harian';

    protected static ?string $label = 'Menu Harian';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Daily Menu Plan')
                    ->schema([
                        Forms\Components\DatePicker::make('menu_date')
                            ->default(now()),

                        Forms\Components\Repeater::make('dailyMenuItems')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('menu_id')
                                    ->relationship('menu', 'menu_name')
                                    ->preload()
                                    ->searchable()
                                    ->getOptionLabelUsing(fn($value): ?string => Menu::find($value)?->menu_name)
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('menu_name')->label('Nama Menu')->required(),
                                    ]),

                                Forms\Components\Select::make('target_group_id')
                                    ->relationship('targetGroup', 'name')
                                    ->required(),

                                Forms\Components\TextInput::make('target_quantity')
                                    ->label('Jumah Target')
                                    ->required()
                                    ->suffix(' Porsi'),
                            ])
                            ->columns(3)
                            ->itemLabel(function (array $state): ?string {
                                // Menampilkan nama menu di setiap item repeater
                                $menuName = '';

                                if (!empty($state['menu_id'])) {
                                    $menu = Menu::find($state['menu_id']);
                                    if ($menu) {
                                        $menuName = $menu->menu_name;
                                    }
                                }

                                return $menuName ?: 'Menu Item';
                            })
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('menu_date')
                    ->date()
                    ->searchable()
                    ->sortable()
                    ->label('Tanggal Menu'),
                Tables\Columns\TextColumn::make('dailyMenuItems.menu.menu_name')
                    ->label('Menu')
                    ->searchable()
                    ->listWithLineBreaks(),
                Tables\Columns\TextColumn::make('dailyMenuItems.targetGroup.name')
                    ->label('Target Group')
                    ->listWithLineBreaks(),
                Tables\Columns\TextColumn::make('dailyMenuItems.target_quantity')
                    ->label('Jumlah Target')
                    ->numeric(
                        decimalPlaces: 0,
                        decimalSeparator: ',',
                        thousandsSeparator: '.'
                    )
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
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                BulkAction::make('export-selected')
                    ->label('Ekspor Daily Menu')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(function (Collection $records) {
                        $ids = $records->pluck('id');
                        $timestamp = Carbon::now()->format('Ymd_His');

                        return Excel::download(
                            new DailyMenuItemsExport($ids),
                            "daily-menu-items_{$timestamp}.xlsx"
                        );
                    }),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDailyMenus::route('/'),
            'create' => Pages\CreateDailyMenu::route('/create'),
            'edit' => Pages\EditDailyMenu::route('/{record}/edit'),
        ];
    }
}
