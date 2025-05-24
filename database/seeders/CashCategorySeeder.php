<?php

namespace Database\Seeders;

use App\Models\CashCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CashCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['name' => 'Donasi Umum', 'slug' => 'donasi-umum', 'type' => 'income'],
            ['name' => 'Subsidi Pemerintah', 'slug' => 'subsidi-pemerintah', 'type' => 'income'],
            ['name' => 'Pembayaran PO', 'slug' => 'pembayaran-po', 'type' => 'expense'],
            ['name' => 'Biaya Operasional', 'slug' => 'biaya-operasional', 'type' => 'expense'],
        ];

        foreach ($data as $item) {
            CashCategory::firstOrCreate(['slug' => $item['slug']], $item);
        }
    }
}