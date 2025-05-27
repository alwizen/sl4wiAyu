<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RecipientResource\Pages;
use App\Filament\Resources\RecipientResource\RelationManagers;
use App\Models\Recipient;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Table;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RecipientResource extends Resource
{
    protected static ?string $model = Recipient::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';

    protected static ?string $navigationGroup = 'Produksi & Pengiriman';

    protected static ?string $navigationLabel = 'Daftar Penerima';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->label('NPSN')
                    ->required(),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                //                Forms\Components\Select::make('target_group_id')
                //                    ->relationship('targetGroup', 'name')
                //                    ->required(),
                Forms\Components\TextInput::make('phone')
                    ->tel()
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('address')
                    ->required(),
                Forms\Components\TextInput::make('total_recipients')
                    ->required()
                    ->numeric()
                    ->default(0),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(50)
            //            ->paginated(false)
            ->columns([
                //                Tables\Columns\TextColumn::make('#')
                //                    ->rowIndex(),
                Tables\Columns\TextColumn::make('code')
                    ->label('NPSN')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Penerima Manfaat')
                    ->searchable(),
                Tables\Columns\TextColumn::make('address')
                    ->label('Alamat')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('No. Telp')
                    ->searchable(),
                //                Tables\Columns\TextColumn::make('targetGroup.name')
                //                    ->numeric()
                //                    ->sortable(),
                Tables\Columns\TextColumn::make('total_recipients')
                    ->label('Total Penerima')
                    ->summarize(Tables\Columns\Summarizers\Sum::make())
                    ->numeric()
                    ->sortable(),
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
                    ExportBulkAction::make(),
                    DeleteBulkAction::make()
                ]),
            ]);
    }

   

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageRecipients::route('/'),
        ];
    }
}
