<?php

namespace App\Filament\Resources\DeliveryResource\Pages;

use App\Filament\Resources\DeliveryResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;

class ManageDeliveries extends ManageRecords
{
    protected static string $resource = DeliveryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('Daftar penerima')
                ->label('Daftar Penerima')
                ->icon('heroicon-o-user-group')
                ->url(route('filament.admin.resources.recipients.index')) // Sesuaikan dengan nama resource tujuan
                ->color('success')
                ->openUrlInNewTab(),
            Actions\CreateAction::make()
                ->label('Buat Jadwal Pengiriman')
                ->icon('heroicon-o-plus')
                ->color('primary'),
        ];
    }

    // protected function afterCreate(): void
    // {
    //     $delivery = $this->record;
    //     $user = auth()->user();

    //     Notification::make()
    //         ->title('Jadwal Pengiriman Ditambahkan')
    //         ->body("Jadwal pengiriman dengan nomor *{$delivery->delivery_number}* berhasil dibuat oleh *{$user->name}*.")
    //         ->success()
    //         ->sendToDatabase($user);
    // }
}
