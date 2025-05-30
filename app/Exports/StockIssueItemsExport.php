<?php

namespace App\Exports;

use App\Models\StockIssueItem;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StockIssueItemsExport implements FromCollection, WithHeadings
{
    protected $stockIssueIds;

    public function __construct($stockIssueIds)
    {
        $this->stockIssueIds = $stockIssueIds;
    }

    public function collection()
    {
        return StockIssueItem::with(['stockIssue', 'warehouseItem'])
            ->whereIn('stock_issue_id', $this->stockIssueIds)
            ->get()
            ->map(function ($item) {
                return [
                    'Tanggal Permintaan' => $item->stockIssue->issue_date ?? '-',
                    'Status' => $item->stockIssue->status ?? '-',
                    'Item Gudang' => $item->warehouseItem->name ?? '-',
                    'Jumlah Diminta' => $item->requested_quantity,
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Tanggal Permintaan',
            'Status',
            'Item Gudang',
            'Jumlah Diminta',
        ];
    }
}
