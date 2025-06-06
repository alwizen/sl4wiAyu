<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        
        $departments = [
            [
                'name' => 'SPV Dapur',
                'salary' => 300000,
                'allowance' => 500000,
                'bonus' => 0,
                'absence_deduction' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Ahli Gizi',
                'salary' => 2500000,
                'allowance' => 500000,
                'bonus' => 0,
                'absence_deduction' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Staf Ahli Gizi',
                'salary' => 2000000,
                'allowance' => 350000,
                'bonus' => 0,
                'absence_deduction' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Akuntan',
                'salary' => 2500000,
                'allowance' => 500000,
                'bonus' => 0,
                'absence_deduction' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Staf Akuntan',
                'salary' => 2000000,
                'allowance' => 350000,
                'bonus' => 0,
                'absence_deduction' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Kepala Gudang',
                'salary' => 2500000,
                'allowance' => 500000,
                'bonus' => 0,
                'absence_deduction' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Staf Gudang',
                'salary' => 2000000,
                'allowance' => 350000,
                'bonus' => 0,
                'absence_deduction' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Kepala Koki',
                'salary' => 2500000,
                'allowance' => 500000,
                'bonus' => 0,
                'absence_deduction' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Tim Produksi',
                'salary' => 2500000,
                'allowance' => 300000,
                'bonus' => 0,
                'absence_deduction' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Tim Cleaning',
                'salary' => 2000000,
                'allowance' => 200000,
                'bonus' => 0,
                'absence_deduction' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Tim Prepairing',
                'salary' => 2000000,
                'allowance' => 200000,
                'bonus' => 0,
                'absence_deduction' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Tim Packing',
                'salary' => 2000000,
                'allowance' => 200000,
                'bonus' => 0,
                'absence_deduction' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Tim Distribusi',
                'salary' => 2000000,
                'allowance' => 200000,
                'bonus' => 0,
                'absence_deduction' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('departments')->insert($departments);
    }
}