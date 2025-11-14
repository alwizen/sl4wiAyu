<?php

namespace Database\Seeders;

use App\Models\Shift;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ShiftSeeder extends Seeder
{
    public function run()
    {
        $shifts = [
            [
                'name' => 'Persiapan',
                'start_time' => '21:00:00',
                'end_time' => '05:00:00',
                'is_overnight' => true,
                'tolerance_late_minutes' => 15,
            ],
            [
                'name' => 'Pengolahan',
                'start_time' => '02:30:00',
                'end_time' => '10:30:00',
                'is_overnight' => false, // Karena 02:30 masih dianggap dini hari tapi tidak melewati tengah malam dari perspektif shift
                'tolerance_late_minutes' => 15,
            ],
            [
                'name' => 'Pemorsian',
                'start_time' => '04:00:00',
                'end_time' => '12:00:00',
                'is_overnight' => false,
                'tolerance_late_minutes' => 15,
            ],
            [
                'name' => 'Distribusi',
                'start_time' => '07:00:00',
                'end_time' => '03:00:00',
                'is_overnight' => true,
                'tolerance_late_minutes' => 15,
            ],
            [
                'name' => 'Cuci Ompreng',
                'start_time' => '13:00:00',
                'end_time' => '21:00:00',
                'is_overnight' => false,
                'tolerance_late_minutes' => 15,
            ],
        ];

        foreach ($shifts as $shift) {
            Shift::create($shift);
        }
    }
}
