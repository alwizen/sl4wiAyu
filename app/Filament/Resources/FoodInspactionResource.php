<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FoodInspactionResource\Pages;
use App\Filament\Resources\FoodInspactionResource\RelationManagers;
use App\Models\FoodInspaction;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FoodInspactionResource extends Resource
{
    protected static ?string $model = FoodInspaction::class;

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'Ahli Gizi';

    // protected static ?string $navigationLabel = 'M';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([

                        Forms\Components\DateTimePicker::make('inspaction_date')
                            ->default(now())
                            ->required(),
                        Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Select::make('menu_id')
                                    ->relationship('menu', 'menu_name'),
                                Toggle::make('is_good')
                            ])
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('inspaction_date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('items.menu.menu_name')
                    ->listWithLineBreaks(),

                Tables\Columns\IconColumn::make('items.is_good')
                    ->label('Kondisi Makanan')
                    ->icon(fn(string $state): string => match ($state) {
                        '1' => 'heroicon-o-check-circle', //nilai baik = 1
                        '0' => 'heroicon-o-x-circle', //nilai tidak baik = 0
                    })
                    ->listWithLineBreaks()
                    ->color(fn(string $state): string => match ($state) {
                        '1' => 'success', //nilai baik = 1
                        '0' => 'danger', //nilai tidak baik = 0
                    }),


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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFoodInspactions::route('/'),
            'create' => Pages\CreateFoodInspaction::route('/create'),
            'edit' => Pages\EditFoodInspaction::route('/{record}/edit'),
        ];
    }
}
