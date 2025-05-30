<?php
namespace App\Exports;

use App\Models\DailyMenuItem;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DailyMenuItemsExport implements FromCollection, WithHeadings
{
    protected $dailyMenuIds;

    public function __construct($dailyMenuIds)
    {
        $this->dailyMenuIds = $dailyMenuIds;
    }

    public function collection()
    {
        return DailyMenuItem::with(['dailyMenu', 'menu', 'targetGroup'])
            ->whereIn('daily_menu_id', $this->dailyMenuIds)
            ->get()
            ->map(function ($item) {
                return [
                    'Tanggal Menu' => $item->dailyMenu->menu_date ?? '-',
                    'Nama Menu' => $item->menu->menu_name ?? '-',
                    'Kelompok Penerima' => $item->targetGroup->name ?? '-',
                    'Jumlah Target (Porsi)' => $item->target_quantity ?? '-',
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Tanggal Menu',
            'Nama Menu',
            'Kelompok Penerima',
            'Jumlah Target (Porsi)',
        ];
    }
}

