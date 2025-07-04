<?php

namespace App\Notifications;

use App\Models\PurchaseOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Filament\Notifications\Notification as FilamentNotification;

class PurchaseOrderApproved extends Notification
{
    use Queueable;

    protected $po;

    /**
     * Create a new notification instance.
     */
    public function __construct(PurchaseOrder $po)
    {
        $this->po = $po;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase($notifiable): array
    {
        return [
            'title' => 'Purchase Order Disetujui',
            'body' => 'PO #' . $this->po->order_number . ' telah disetujui oleh super admin.',
            'icon' => 'heroicon-o-check-circle',
            'color' => 'success',
            'actions' => [
                [
                    'label' => 'Lihat PO',
                    'url' => route('filament.admin.resources.purchase-orders.edit', $this->po->id),
                ],
            ],
            // Data tambahan untuk referensi
            'purchase_order_id' => $this->po->id,
            'order_number' => $this->po->order_number,
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Purchase Order Disetujui',
            'body' => 'PO #' . $this->po->order_number . ' telah disetujui oleh super admin.',
            'icon' => 'heroicon-o-check-circle',
            'color' => 'success',
            'purchase_order_id' => $this->po->id,
            'order_number' => $this->po->order_number,
        ];
    }
}