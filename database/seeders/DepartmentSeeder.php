<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $departments = [
            [
                'name' => 'SPV Dapur',
                'salary' => 200000,
                'allowance' => 35000,
                'bonus' => 0,
                'absence_deduction' => 0,
                'permit_amount' => 50000,
                'start_time' => null,
                'end_time' => null,
                'is_overnight' => false,
                'tolerance_late_minutes' => 15,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Ahli Gizi',
                'salary' => 75000,
                'allowance' => 35000,
                'bonus' => 0,
                'absence_deduction' => 0,
                'permit_amount' => 50000,
                'start_time' => null,
                'end_time' => null,
                'is_overnight' => false,
                'tolerance_late_minutes' => 15,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Akuntan',
                'salary' => 20000,
                'allowance' => 50000,
                'bonus' => 0,
                'absence_deduction' => 0,
                'permit_amount' => 50000,
                'start_time' => null,
                'end_time' => null,
                'is_overnight' => false,
                'tolerance_late_minutes' => 15,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Kepala Koki',
                'salary' => 100000,
                'allowance' => 50000,
                'bonus' => 0,
                'absence_deduction' => 0,
                'permit_amount' => 50000,
                'start_time' => '02:30:00',
                'end_time' => '10:30:00',
                'is_overnight' => false,
                'tolerance_late_minutes' => 15,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Tim Produksi',
                'salary' => 100000,
                'allowance' => 30000,
                'bonus' => 0,
                'absence_deduction' => 0,
                'permit_amount' => 50000,
                'start_time' => '02:30:00',
                'end_time' => '10:30:00',
                'is_overnight' => false,
                'tolerance_late_minutes' => 15,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Tim Cleaning (Ompreng)',
                'salary' => 100000,
                'allowance' => 20000,
                'bonus' => 0,
                'absence_deduction' => 0,
                'permit_amount' => 50000,
                'start_time' => '13:00:00',
                'end_time' => '21:00:00',
                'is_overnight' => false,
                'tolerance_late_minutes' => 15,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Tim Prepairing',
                'salary' => 100000,
                'allowance' => 20000,
                'bonus' => 0,
                'absence_deduction' => 0,
                'permit_amount' => 50000,
                'start_time' => '21:00:00',
                'end_time' => '05:00:00',
                'is_overnight' => true,
                'tolerance_late_minutes' => 15,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Tim Packing',
                'salary' => 100000,
                'allowance' => 20000,
                'bonus' => 0,
                'absence_deduction' => 0,
                'permit_amount' => 50000,
                'start_time' => '04:00:00',
                'end_time' => '12:00:00',
                'is_overnight' => false,
                'tolerance_late_minutes' => 15,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Tim Distribusi',
                'salary' => 100000,
                'allowance' => 20000,
                'bonus' => 0,
                'absence_deduction' => 0,
                'permit_amount' => 50000,
                'start_time' => '07:00:00',
                'end_time' => '15:00:00',
                'is_overnight' => false,
                'tolerance_late_minutes' => 15,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('departments')->insert($departments);
    }
}
