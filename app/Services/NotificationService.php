<?php

namespace App\Services;

use App\Models\User;
use App\Models\PurchaseOrder;
use App\Notifications\PurchaseOrderApproved;
use Filament\Notifications\Notification;

class NotificationService
{
    /**
     * Send approval notification to PO creator
     */
    public static function sendPurchaseOrderApprovalNotification(PurchaseOrder $po, User $approver)
    {
        // Send database notification
        if ($po->creator) {
            $po->creator->notify(new PurchaseOrderApproved($po));
        }
        
        // Send real-time Filament notification
        Notification::make()
            ->title('Purchase Order Disetujui')
            ->body("PO {$po->order_number} berhasil disetujui oleh {$approver->name}")
            ->icon('heroicon-o-check-circle')
            ->iconColor('success')
            ->actions([
                \Filament\Notifications\Actions\Action::make('view')
                    ->label('Lihat PO')
                    ->url(route('filament.admin.resources.purchase-orders.edit', $po->id))
                    ->markAsRead(),
            ])
            ->sendToDatabase($po->creator);
    }
}