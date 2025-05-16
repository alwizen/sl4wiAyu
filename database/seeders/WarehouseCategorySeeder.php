<?php

namespace Database\Seeders;

use App\Models\WarehouseCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WarehouseCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Kering',
            'Basah',
            'Bumbu',
        ];

        foreach ($categories as $categoryName) {
            WarehouseCategory::firstOrCreate(
                ['name' => $categoryName],
                ['name' => $categoryName]
            );
        }
    }
}
