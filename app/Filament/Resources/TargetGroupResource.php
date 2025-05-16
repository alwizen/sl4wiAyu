<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TargetGroupResource\Pages;
use App\Filament\Resources\TargetGroupResource\RelationManagers;
use App\Models\TargetGroup;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TargetGroupResource extends Resource
{
    protected static ?string $model = TargetGroup::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama Kelompok')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('energy')
                    ->label('Energi (kkal)')
                    ->numeric()
                    ->required(),


                Forms\Components\TextInput::make('protein')
                    ->label('Protein (gr)')
                    ->numeric()
                    ->required(),

                Forms\Components\TextInput::make('fat')
                    ->label('Lemak (gr)')
                    ->numeric()
                    ->required(),

                Forms\Components\TextInput::make('carb')
                    ->label('Karbohidrat (gr)')
                    ->numeric()
                    ->required(),

                Forms\Components\TextInput::make('vitamin')
                    ->label('Vitamin (gr)')
                    ->numeric(),

                Forms\Components\TextInput::make('mineral')
                    ->label('Minaral (gr)')
                    ->numeric(),
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('energy')
                    ->label('Energi (kkal)')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('protein')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('fat')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('carb')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('vitamin')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('mineral')
                    ->searchable()
                ->numeric(),
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
            'index' => Pages\ListTargetGroups::route('/'),
            'create' => Pages\CreateTargetGroup::route('/create'),
            'edit' => Pages\EditTargetGroup::route('/{record}/edit'),
        ];
    }
}
