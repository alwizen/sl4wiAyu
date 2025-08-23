<?php

namespace App\Jobs;

use App\Models\SppgPurchaseOrder;
use App\Services\HubClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class PushPoToHubJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $poId;

    public function __construct(int $poId)
    {
        $this->poId = $poId;
    }

    public function handle(): void
    {
        $po = SppgPurchaseOrder::with(['items.warehouseItem', 'creator'])->findOrFail($this->poId);

        $payload = [
            'po_number'     => $po->po_number,
            'requested_at'  => optional($po->requested_at)->toDateString(),
            'delivery_time' => $po->delivery_time,
            'submitted_at'  => Carbon::now('UTC')->toIso8601String(),
            'notes'         => $po->notes,
            'items' => $po->items->map(function ($it) {
                return [
                    'id' => $it->id,
                    'warehouse_item_id' => $it->warehouse_item_id,
                    'qty' => (string)$it->qty,
                    'unit' => $it->unit,
                    'warehouse_item' => [
                        'name' => optional($it->warehouseItem)->name ?? $it->item_name ?? 'N/A',
                        'unit' => optional($it->warehouseItem)->unit ?? $it->unit ?? 'unit',
                    ],
                    'note' => $it->note,
                ];
            })->values()->all(),
            'external' => [
                'sppg_po_id'   => $po->id,
                'creator_id'   => $po->created_by,
                'creator_name' => optional($po->creator)->name,
            ],
        ];

        $resp = HubClient::submitIntake($payload);

        $po->update([
            'hub_intake_id' => $resp['intake_id'] ?? null,
            'hub_synced_at' => Carbon::now(),
            'hub_last_error' => null,
        ]);
    }
}
