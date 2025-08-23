<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SppgPurchaseOrder;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class SppgPurchaseOrderController extends Controller
{
    /**
     * Display a listing of purchase orders.
     */
    public function index(Request $request): JsonResponse
    {
        $query = SppgPurchaseOrder::with(['items.warehouseItem', 'creator']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->has('date_from')) {
            $query->whereDate('requested_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('requested_at', '<=', $request->date_to);
        }

        // Search by PO number
        if ($request->has('search')) {
            $query->where('po_number', 'like', '%' . $request->search . '%');
        }

        $purchaseOrders = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json($purchaseOrders);
    }

    /**
     * Store a new purchase order.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'requested_at' => 'required|date',
                'delivery_time' => 'required',
                'notes' => 'nullable|string',
                'items' => 'required|array|min:1',
                'items.*.manual_entry' => 'boolean',
                'items.*.warehouse_item_id' => 'nullable|exists:warehouse_items,id',
                'items.*.item_name' => 'nullable|string|required_if:items.*.manual_entry,true',
                'items.*.qty' => 'required|numeric|min:0.001',
                'items.*.unit' => 'nullable|string',
                'items.*.note' => 'nullable|string',
            ]);

            // Generate PO number
            $validated['po_number'] = SppgPurchaseOrder::generateNumber();
            $validated['status'] = 'Draft';
            $validated['created_by'] = auth()->id() ?? 1; // Default untuk testing

            // Create purchase order
            $purchaseOrder = SppgPurchaseOrder::create([
                'po_number' => $validated['po_number'],
                'requested_at' => $validated['requested_at'],
                'delivery_time' => $validated['delivery_time'],
                'notes' => $validated['notes'] ?? null,
                'status' => $validated['status'],
                'created_by' => $validated['created_by'],
            ]);

            // Create items
            foreach ($validated['items'] as $itemData) {
                $purchaseOrder->items()->create($itemData);
            }

            // Load relationships for response
            $purchaseOrder->load(['items.warehouseItem', 'creator']);

            return response()->json([
                'success' => true,
                'message' => 'Purchase Order created successfully',
                'data' => $purchaseOrder
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create purchase order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified purchase order.
     */
    public function show(SppgPurchaseOrder $sppgPurchaseOrder): JsonResponse
    {
        $sppgPurchaseOrder->load(['items.warehouseItem', 'creator']);

        return response()->json([
            'success' => true,
            'data' => $sppgPurchaseOrder
        ]);
    }

    /**
     * Update the specified purchase order.
     */
    public function update(Request $request, SppgPurchaseOrder $sppgPurchaseOrder): JsonResponse
    {
        try {
            if ($sppgPurchaseOrder->status !== 'Draft') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only Draft Purchase Orders can be updated'
                ], 422);
            }

            $validated = $request->validate([
                'requested_at' => 'required|date',
                'delivery_time' => 'required',
                'notes' => 'nullable|string',
                'items' => 'required|array|min:1',
                'items.*.manual_entry' => 'boolean',
                'items.*.warehouse_item_id' => 'nullable|exists:warehouse_items,id',
                'items.*.item_name' => 'nullable|string|required_if:items.*.manual_entry,true',
                'items.*.qty' => 'required|numeric|min:0.001',
                'items.*.unit' => 'nullable|string',
                'items.*.note' => 'nullable|string',
            ]);

            // Update purchase order
            $sppgPurchaseOrder->update([
                'requested_at' => $validated['requested_at'],
                'delivery_time' => $validated['delivery_time'],
                'notes' => $validated['notes'] ?? null,
            ]);

            // Update items - delete old and create new
            $sppgPurchaseOrder->items()->delete();
            foreach ($validated['items'] as $itemData) {
                $sppgPurchaseOrder->items()->create($itemData);
            }

            $sppgPurchaseOrder->load(['items.warehouseItem', 'creator']);

            return response()->json([
                'success' => true,
                'message' => 'Purchase Order updated successfully',
                'data' => $sppgPurchaseOrder
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update purchase order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified purchase order.
     */
    public function destroy(SppgPurchaseOrder $sppgPurchaseOrder): JsonResponse
    {
        try {
            $sppgPurchaseOrder->delete();

            return response()->json([
                'success' => true,
                'message' => 'Purchase Order deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete purchase order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Submit purchase order (Draft -> Submitted).
     */
    public function submit(SppgPurchaseOrder $sppgPurchaseOrder): JsonResponse
    {
        if ($sppgPurchaseOrder->status !== 'Draft') {
            return response()->json([
                'success' => false,
                'message' => 'Only Draft Purchase Orders can be submitted'
            ], 422);
        }

        try {
            $sppgPurchaseOrder->update(['status' => 'Submitted']);
            $sppgPurchaseOrder->load(['items.warehouseItem', 'creator']);

            // Di sini bisa tambahkan logic untuk kirim notifikasi ke supplier/yayasan
            // $this->notifySupplierAndYayasan($sppgPurchaseOrder);

            return response()->json([
                'success' => true,
                'message' => 'Purchase Order submitted successfully',
                'data' => $sppgPurchaseOrder
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit purchase order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reopen purchase order (Submitted -> Draft).
     */
    public function reopen(SppgPurchaseOrder $sppgPurchaseOrder): JsonResponse
    {
        if ($sppgPurchaseOrder->status !== 'Submitted') {
            return response()->json([
                'success' => false,
                'message' => 'Only Submitted Purchase Orders can be reopened'
            ], 422);
        }

        try {
            $sppgPurchaseOrder->update(['status' => 'Draft']);
            $sppgPurchaseOrder->load(['items.warehouseItem', 'creator']);

            return response()->json([
                'success' => true,
                'message' => 'Purchase Order reopened successfully',
                'data' => $sppgPurchaseOrder
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reopen purchase order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get purchase order statistics.
     */
    public function stats(): JsonResponse
    {
        $stats = [
            'total' => SppgPurchaseOrder::count(),
            'draft' => SppgPurchaseOrder::where('status', 'Draft')->count(),
            'submitted' => SppgPurchaseOrder::where('status', 'Submitted')->count(),
            'today' => SppgPurchaseOrder::whereDate('created_at', today())->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    // Private method untuk notification (opsional)
    // private function notifySupplierAndYayasan($purchaseOrder)
    // {
    //     // Logic untuk kirim notification
    //     // Bisa via webhook, pusher, email, dll
    // }
}
