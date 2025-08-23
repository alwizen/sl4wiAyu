<?php

namespace App\Services;

use App\Models\SppgPurchaseOrder;
use Illuminate\Support\Facades\Http;

class SendPoToHub
{
    public static function dispatch(SppgPurchaseOrder $po): array
    {
        $payload = [
            'source' => config('app.kitchen_code', env('KITCHEN_SOURCE', 'K-XX')),
            'external_id' => $po->po_number, // atau field khusus external_id kalau ada
            'requested_at' => $po->requested_at->format('Y-m-d'),
            'delivery_time' => optional($po->delivery_time)->format('H:i'),
            'notes' => $po->notes,
            'items' => $po->items->map(function ($it) {
                return [
                    'warehouse_item_id' => $it->warehouse_item_id,
                    'sku' => null,
                    'item_name' => $it->item_name,
                    'qty' => (float) $it->qty,
                    'unit' => $it->unit,
                    'note' => $it->note,
                ];
            })->values()->all(),
        ];

        $res = Http::withToken(env('HUB_API_TOKEN'))
            ->acceptJson()
            ->post(rtrim(env('HUB_API_BASE'), '/') . '/api/kitchen-pos', $payload);

        return [
            'ok' => $res->successful(),
            'status' => $res->status(),
            'data' => $res->json(),
        ];
    }
}
