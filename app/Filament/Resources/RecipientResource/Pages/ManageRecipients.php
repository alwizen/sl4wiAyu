<?php

namespace App\Filament\Resources\RecipientResource\Pages;

use App\Filament\Imports\RecipientImporter;
use App\Filament\Resources\RecipientResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ManageRecords;

class ManageRecipients extends ManageRecords
{
    protected static string $resource = RecipientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ImportAction::make('importRecipients')
                ->importer(RecipientImporter::class)
                ->label('Import Penerima')
                ->color('warning'),
            Actions\CreateAction::make()
                ->label('Buat Penerima Manfaat')
                ->icon('heroicon-o-plus')
                ->color('primary'),
        ];
    }
}
