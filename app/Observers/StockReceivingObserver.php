<?php

namespace App\Observers;

use App\Models\StockReceiving;

class StockReceivingObserver
{
    public function saved(StockReceiving $receiving): void
    {
        $this->updatePurchaseOrderStatus($receiving);
    }

    public function deleted(StockReceiving $receiving): void
    {
        $this->updatePurchaseOrderStatus($receiving);
    }

    private function updatePurchaseOrderStatus(StockReceiving $receiving): void
    {
        $po = $receiving->purchaseOrder;

        if (!$po) {
            return;
        }

        // Pastikan semua relasi tersedia untuk perhitungan
        $po->loadMissing('items', 'receivings.stockReceivingItems');

        // Cek apakah benar-benar sudah fully delivered
        $isActuallyComplete = $po->isFullyDelivered();

        // Update status berdasarkan kondisi sebenarnya
        if ($isActuallyComplete && !$po->is_received_complete) {
            $po->is_received_complete = true;
            $po->save();
        } elseif (!$isActuallyComplete && $po->is_received_complete) {
            // Jika ternyata belum complete (mungkin ada koreksi), ubah kembali
            $po->is_received_complete = false;
            $po->save();
        }
    }
}
