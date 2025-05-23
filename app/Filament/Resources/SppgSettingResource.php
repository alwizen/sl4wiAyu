<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SppgSettingResource\Pages;
use App\Filament\Resources\SppgSettingResource\RelationManagers;
use App\Models\SppgSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SppgSettingResource extends Resource
{
    protected static ?string $model = SppgSetting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog';

    protected static ?string $navigationGroup = 'Settings';

    public static function form(Form $form): Form
    {
            return $form->schema([
                Forms\Components\TextInput::make('sppg_name')->required()->label('Nama SPPG'),
                Forms\Components\Textarea::make('address')->rows(3)->label('Alamat'),

                Forms\Components\FileUpload::make('logo_light')
                    ->image()
                    ->directory('logos')
                    ->label('Logo (Light Mode)'),

                Forms\Components\FileUpload::make('logo_dark')
                    ->image()
                    ->directory('logos')
                    ->label('Logo (Dark Mode)'),

                Forms\Components\FileUpload::make('favicon')
                    ->image()
                    ->directory('logos')
                    ->label('Favicon'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sppg_name')
                    ->searchable(),
                Tables\Columns\ImageColumn::make('logo_light')
                    ->searchable(),
                Tables\Columns\ImageColumn::make('logo_dark')
                    ->searchable(),
                Tables\Columns\ImageColumn::make('favicon')
                    ->searchable(),
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
                //
            ])
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
            'index' => Pages\ListSppgSettings::route('/'),
            'create' => Pages\CreateSppgSetting::route('/create'),
            'edit' => Pages\EditSppgSetting::route('/{record}/edit'),
        ];
    }
}
