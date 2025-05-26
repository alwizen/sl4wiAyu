<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockIssueResource\Pages;
use App\Filament\Resources\StockIssueResource\RelationManagers;
use App\Models\StockIssue;
use App\Models\WarehouseItem;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StockIssueResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = StockIssue::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-on-square';

    protected static ?string $navigationGroup = 'Pengadaan & Permintaan';

    protected static ?string $navigationLabel = 'Permintaan Bahan Masak';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        DatePicker::make('issue_date')
                            ->label('Tanggal Permintaan')
                            ->required(),

                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'Draft' => 'Diminta',
                                'Submitted' => 'Disiapkan',
                            ])
                            ->default('Draft')
                            ->disabled(), // status akan diubah oleh gudang, bukan saat input admin
                    ]),

                Card::make()
                    ->schema([
                        Repeater::make('items')
                            ->label('Daftar Item yang Diminta')
                            ->relationship()
                            ->schema([
                                Select::make('warehouse_item_id')
                                    ->label('Item Gudang')
                                    ->options(WarehouseItem::all()->pluck('name', 'id'))
                                    ->searchable()
                                    ->required(),

                                TextInput::make('requested_quantity')
                                    ->label('Jumlah Diminta')
                                    ->numeric()
                                    ->required(),
                            ])
                            ->columns(2),
                    ]),
            ]);
    }



    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('issue_date')
                    ->label('Tanggal Permintaan')
                    ->date('d-m-Y')
                    ->sortable(),

                BadgeColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(function ($state) {
                        return $state === 'Submitted' ? 'Selesai' : $state;
                    })
                    ->colors([
                        'gray' => 'Draft',
                        'success' => 'Submitted', // Tetap menggunakan 'Submitted' untuk pencocokan warna
                    ])
                    ->sortable()
                    ->searchable(),

                TextColumn::make('items_count')
                    ->label('Jumlah Item')
                    ->counts('items'),

                TextColumn::make('updated_at')
                    ->label('Terakhir Diperbarui')
                    ->date('d-m-Y H:i')


            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('Lihat Detail')
                    ->icon('heroicon-o-eye')
                    ->slideOver()
                    ->infolist([
                        Section::make('Informasi Umum')
                            ->schema([
                                TextEntry::make('issue_date')->label('Tanggal Permintaan'),
                                TextEntry::make('status')->label('Status'),
                            ]),

                        Section::make('Daftar Item')
                            ->schema([
                                RepeatableEntry::make('items')
                                    ->label('Item Diminta')
                                    ->schema([
                                        TextEntry::make('warehouseItem.name')->label('Nama Barang'),
                                        TextEntry::make('requested_quantity')->label('Jumlah Diminta')->numeric(),
                                    ])
                                    ->columns(2),
                            ]),
                    ]),

                Tables\Actions\Action::make('mark_prepared')
                    ->label('Tandai Disiapkan')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->visible(fn($record) =>
                        $record->status === 'Draft' &&
                        auth()->user()?->can('mark_prepared_stock::issue')
                    )
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update(['status' => 'Submitted']);

                        foreach ($record->items as $item) {
                            $warehouseItem = $item->warehouseItem;
                            $warehouseItem->decrement('stock', $item->requested_quantity);
                        }
                    }),

                Tables\Actions\EditAction::make(),
                    // ->visible(fn($record) => $record->status === 'Draft'),
                Tables\Actions\DeleteAction::make()
                    // ->visible(fn($record) => $record->status === 'Draft'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
            'mark_prepared'
        ];
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
            'index' => Pages\ListStockIssues::route('/'),
            'create' => Pages\CreateStockIssue::route('/create'),
            'edit' => Pages\EditStockIssue::route('/{record}/edit'),
        ];
    }
}
