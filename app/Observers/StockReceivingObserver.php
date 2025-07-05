<?php

namespace App\Observers;

use App\Models\StockReceiving;

class StockReceivingObserver
{
    public function saved(StockReceiving $receiving): void
    {
        $po = $receiving->purchaseOrder;

        if ($po && $po->isFullyDelivered()) {
            $po->is_received_complete = true;
            $po->save();
        }
    }
}
