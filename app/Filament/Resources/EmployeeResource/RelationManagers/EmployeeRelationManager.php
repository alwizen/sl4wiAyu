<?php

namespace App\Filament\Resources\EmployeeResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class EmployeeRelationManager extends RelationManager
{
    protected static string $relationship = 'payrolls';
    
    protected static ?string $title = 'Riwayat Penggajian';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('total_thp')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('total_thp')
            ->columns([
                Tables\Columns\TextColumn::make('employee.name')
                    ->label('Nama Relawan'),
                Tables\Columns\TextColumn::make('month')
                    ->label('Bulan')
                    ->date('F Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('work_days')
                    ->label('Jumlah Hari Masuk')
                    ->suffix(' hari')
                    ->sortable(),
                Tables\Columns\TextColumn::make('absences')
                    ->label('Jumlah Ketidakhadiran')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_thp')
                    ->label('Total THP')
                    ->summarize(Sum::make())
                    ->money('IDR')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
    
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    ExportBulkAction::make()
                        ->label('Ekspor Data Penggajian')
                        // ->icon('heroicon-o-download')
                        // ->fileName(fn () => 'penggajian-' . now()->format('Y-m-d') . '.xlsx'),
                ]),
            ]);
    }
}
