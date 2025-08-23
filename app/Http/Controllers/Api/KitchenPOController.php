<?php
// app/Http/Controllers/Api/KitchenPOController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreKitchenPORequest;
use App\Models\SppgPurchaseOrder;
use App\Models\SppgPurchaseOrderItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class KitchenPOController extends Controller
{
    public function store(StoreKitchenPORequest $request): JsonResponse
    {
        $data = $request->validated();

        // idempoten: cek existing by source + external_id
        $exists = SppgPurchaseOrder::query()
            ->where('source', $data['source'])
            ->where('external_id', $data['external_id'])
            ->first();

        if ($exists) {
            return response()->json([
                'hub_po_id' => $exists->id,
                'status'    => $exists->status,
                'message'   => 'PO sudah terdaftar (idempotent).',
            ], 200);
        }

        DB::transaction(function () use (&$po, $data) {
            $po = SppgPurchaseOrder::create([
                'source'        => $data['source'],
                'external_id'   => $data['external_id'],
                'po_number'     => $data['external_id'],   // atau generate sendiri kalau beda
                'requested_at'  => $data['requested_at'],
                'delivery_time' => $data['delivery_time'],
                'notes'         => $data['notes'] ?? null,
                'status'        => 'Submitted',
                'created_by'    => auth()->id(),           // atau null kalau bukan user
            ]);

            foreach ($data['items'] as $row) {
                SppgPurchaseOrderItem::create([
                    'sppg_purchase_order_id' => $po->id,
                    'warehouse_item_id'      => $row['warehouse_item_id'] ?? null,
                    'item_name'              => $row['item_name'] ?? null,
                    'qty'                    => $row['qty'],
                    'unit'                   => $row['unit'] ?? null,
                    'note'                   => $row['note'] ?? null,
                ]);
            }
        });

        return response()->json([
            'hub_po_id'       => $po->id,
            'status'          => $po->status,
            'received_items'  => $po->items()->count(),
            'unmapped_items'  => $po->items()->whereNull('warehouse_item_id')->count(),
            'message'         => 'PO berhasil diterima.',
        ], 201);
    }

    public function show(string $source, string $externalId): JsonResponse
    {
        $po = SppgPurchaseOrder::query()
            ->where('source', $source)
            ->where('external_id', $externalId)
            ->with('items')
            ->first();

        if (! $po) {
            return response()->json(['error' => true, 'message' => 'PO tidak ditemukan.'], 404);
        }

        return response()->json([
            'hub_po_id'      => $po->id,
            'status'         => $po->status,
            'requested_at'   => $po->requested_at?->format('Y-m-d'),
            'delivery_time'  => $po->delivery_time?->format('H:i'),
            'notes'          => $po->notes,
            'items'          => $po->items->map(fn($it) => [
                'id'        => $it->id,
                'item_name' => $it->item_name ?? optional($it->warehouseItem)->name,
                'qty'       => (float) $it->qty,
                'unit'      => $it->unit,
                'note'      => $it->note,
            ]),
        ]);
    }
}
