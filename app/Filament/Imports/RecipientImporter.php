<?php

namespace App\Filament\Imports;

use App\Models\Recipient;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class RecipientImporter extends Importer
{
    protected static ?string $model = Recipient::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('code')
                ->label('NPSN')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('phone')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('address')
                ->requiredMapping()
                ->rules(['required']),
            ImportColumn::make('total_recipients')
                ->numeric()
                ->rules(['numeric']),
        ];
    }

    public function resolveRecord(): ?Recipient
    {
        // return Recipient::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new Recipient();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your recipient import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
