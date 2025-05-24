<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory(1)->create();

        User::factory()->create([
            'name' => 'admin',
            'email' => 'admin@admin.com',
        ]);

        $this->call(
            [
                // BookSeeder::class,
                WarehouseCategorySeeder::class,
//                NutrientSeeder::class,
                TargetGroupSeeder::class,
                WarehouseItemSeeder::class,
                DepartmentSeeder::class,
                RecipientSeeder::class,
                CashCategorySeeder::class
            ]
        );
    }
}
