<?php

namespace App\Filament\Resources\EmployeeResource\Pages;

use App\Filament\Resources\EmployeeResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateEmployee extends CreateRecord
{
    protected static string $resource = EmployeeResource::class;

    protected static bool $canCreateAnother = false;

    //customize redirect after create
    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    // protected function afterCreate(): void
    // {
    //     $delivery = $this->record;
    //     $currentUser = auth()->user();

    //     // Siapkan notifikasi
    //     $notification = Notification::make()
    //         ->title('Jadwal Pengiriman Ditambahkan')
    //         ->body("Jadwal pengiriman dengan nomor *{$delivery->delivery_number}* berhasil dibuat oleh *{$currentUser->name}*.")
    //         ->success();

    //     // 1. Kirim ke user yang sedang login
    //     $notification->sendToDatabase($currentUser);

    //     // 2. Kirim ke semua super_admin KECUALI user yang sedang login
    //     \App\Models\User::role('super_admin')
    //         ->where('id', '!=', $currentUser->id)
    //         ->get()
    //         ->each(function ($admin) use ($notification) {
    //             $notification->sendToDatabase($admin);
    //         });
    // }
}
