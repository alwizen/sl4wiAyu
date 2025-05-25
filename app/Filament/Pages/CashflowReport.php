<?php

namespace App\Filament\Pages;

use App\Models\CashTransaction;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;

class CashflowReport extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Keuangan';
    protected static string $view = 'filament.pages.cashflow-report';
    protected static ?string $title = 'Laporan Arus Kas';

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getFilteredQuery())
            ->paginated([50, 100, 'all'])
            ->columns([
                TextColumn::make('transaction_date')->label('Tanggal')->date()->sortable(),
                TextColumn::make('transaction_code')->label('Kode Transaksi')->searchable(),
                TextColumn::make('category.name')->label('Kategori'),
                TextColumn::make('category.type')
                    ->label('Tipe Kategori')
                    ->badge()
                    ->color(fn($state) => $state === 'income' ? 'success' : 'danger'),
                TextColumn::make('amount')->label('Jumlah')->money('IDR'),
                TextColumn::make('methode')->label('Metode'),
            ])
            ->filters([
                Filter::make('transaction_date')
                    ->form([
                        DatePicker::make('from')->label('Dari Tanggal'),
                        DatePicker::make('until')->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['from'], fn($q) => $q->whereDate('transaction_date', '>=', $data['from']))
                            ->when($data['until'], fn($q) => $q->whereDate('transaction_date', '<=', $data['until']));
                    }),
            ])
            ->headerActions([
                ExportAction::make()->label('Export Excel'),
            ]);
    }

    protected function getFilteredQuery(): \Closure
    {
        return function () {
            return CashTransaction::query()->with('category');
        };
    }

    public function getSummary(): array
    {
        $query = $this->getFilteredTableQuery()->get();

        $income = $query->where('category.type', 'income')->sum('amount');
        $expense = $query->where('category.type', 'expense')->sum('amount');
        $balance = $income - $expense;

        return compact('income', 'expense', 'balance');
    }
}
