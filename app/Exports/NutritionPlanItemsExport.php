<?php

namespace App\Exports;

use App\Models\NutritionPlanItem;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class NutritionPlanItemsExport implements FromCollection, WithHeadings
{
    protected $nutritionPlanIds;

    public function __construct($nutritionPlanIds)
    {
        $this->nutritionPlanIds = $nutritionPlanIds;
    }

    public function collection()
    {
        return NutritionPlanItem::with(['nutritionPlan.dailyMenu.menu', 'menu', 'targetGroup'])
            ->whereIn('nutrition_plan_id', $this->nutritionPlanIds) //  filter berdasarkan nutrition_plan_id
            ->get()
            ->map(function ($item) {
                return [
                    'Tanggal Rencana' => $item->nutritionPlan->nutrition_plan_date ?? '-',
                    // 'Menu Harian' => $item->nutritionPlan->dailyMenu->menu->menu_name ?? '-',
                    'Nama Menu' => $item->menu->menu_name ?? '-',
                    'Kelompok Penerima' => $item->targetGroup->name ?? '-',
                    'Energi (kkal)' => $item->energy,
                    'Protein (gr)' => $item->protein,
                    'Lemak (gr)' => $item->fat,
                    'Karbohidrat (gr)' => $item->carb,
                    'Serat (gr)' => $item->serat,
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Tanggal Rencana',
            // 'Menu Harian',
            'Nama Menu',
            'Kelompok Penerima',
            'Energi (kkal)',
            'Protein (gr)',
            'Lemak (gr)',
            'Karbohidrat (gr)',
            'Serat (gr)',
        ];
    }
}
