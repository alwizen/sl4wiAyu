<?php

namespace Database\Seeders;

use App\Models\TargetGroup;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TargetGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $targetGroups = [
            [
                'name' => 'Siswa TK/PAUD',
                'energy' => 328,
                'protein' => 23.4,
                'fat' => 25,
                'carb' => 20.9,
                'vitamin' => 0,
                'mineral' => 0,
            ],
            [
                'name' => 'Siswa SD/MI (kelas 1-3)',
                'energy' => 368.8,
                'protein' => 22.3,
                'fat' => 23.1,
                'carb' => 30.4,
                'vitamin' => 0,
                'mineral' => 0,
            ],
            [
                'name' => 'Siswa SD/MI (kelas 4-6)',
                'energy' => 531,
                'protein' => 32.2,
                'fat' => 30.9,
                'carb' => 30.8,
                'vitamin' => 0,
                'mineral' => 0,
            ],
            [
                'name' => 'Siswa SMP/MTs',
                'energy' => 719,
                'protein' => 32.4,
                'fat' => 34.8,
                'carb' => 37,
                'vitamin' => 0,
                'mineral' => 0,
            ],
            [
                'name' => 'Siswa SMA/SMK/MA',
                'energy' => 762.5,
                'protein' => 32.1,
                'fat' => 36,
                'carb' => 40.4,
                'vitamin' => 0,
                'mineral' => 0,
            ],
            [
                'name' => 'Ibu Hamil',
                'energy' => 818,
                'protein' => 33.3,
                'fat' => 32.4,
                'carb' => 31.9,
                'vitamin' => 0,
                'mineral' => 0,
            ],
            [
                'name' => 'Ibu Menyusui',
                'energy' => 818,
                'protein' => 35.2,
                'fat' => 32.1,
                'carb' => 30.8,
                'vitamin' => 0,
                'mineral' => 0,
            ],
            [
                'name' => 'Anak Balita',
                'energy' => 342,
                'protein' => 24.4,
                'fat' => 27.6,
                'carb' => 20.6,
                'vitamin' => 0,
                'mineral' => 0,
            ],
        ];

        foreach ($targetGroups as $group) {
            TargetGroup::create($group);
        }
    }
}
