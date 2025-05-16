<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WarehouseItem;

class WarehouseItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['name' => 'ayam', 'category' => 1, 'unit' => 'kg'],
            ['name' => 'bakso', 'category' => 1, 'unit' => 'butir'],
            ['name' => 'telur', 'category' => 1, 'unit' => 'kg'],
            ['name' => 'tempe', 'category' => 1, 'unit' => 'papan'],
            ['name' => 'tahu', 'category' => 1, 'unit' => 'butir'],
            ['name' => 'naget', 'category' => 1, 'unit' => 'pak'],
            ['name' => 'susu/yakult', 'category' => 1, 'unit' => 'botol'],
            ['name' => 'tepung terigu', 'category' => 2, 'unit' => 'kg'],
            ['name' => 'tepung beras', 'category' => 2, 'unit' => 'kg'],
            ['name' => 'panir', 'category' => 2, 'unit' => 'kg'],
            ['name' => 'beras', 'category' => 2, 'unit' => 'kg'],
            ['name' => 'bawang merah', 'category' => 3, 'unit' => 'kg'],
            ['name' => 'bawang putih', 'category' => 3, 'unit' => 'kg'],
            ['name' => 'kemiri', 'category' => 3, 'unit' => 'ons'],
            ['name' => 'lada', 'category' => 3, 'unit' => 'ons'],
            ['name' => 'kecap', 'category' => 3, 'unit' => 'pouch'],
            ['name' => 'saus teriyaki', 'category' => 3, 'unit' => 'botol'],
            ['name' => 'saori', 'category' => 3, 'unit' => 'sachet'],
            ['name' => 'cabe merah', 'category' => 3, 'unit' => 'kg'],
            ['name' => 'cesim', 'category' => 1, 'unit' => 'ikat'],
            ['name' => 'wortel', 'category' => 1, 'unit' => 'kg'],
            ['name' => 'daun bawang', 'category' => 1, 'unit' => 'ikat'],
            ['name' => 'kembang kol', 'category' => 1, 'unit' => 'kg'],
            ['name' => 'putren', 'category' => 1, 'unit' => 'kg'],
            ['name' => 'pakcoy', 'category' => 1, 'unit' => 'ikat'],
            ['name' => 'buncis', 'category' => 1, 'unit' => 'kg'],
            ['name' => 'jagung', 'category' => 1, 'unit' => 'batang'],
            ['name' => 'sawi putih', 'category' => 1, 'unit' => 'kg'],
            ['name' => 'bombay', 'category' => 1, 'unit' => 'buah'],
            ['name' => 'jeruk', 'category' => 1, 'unit' => 'buah'],
            ['name' => 'semangka', 'category' => 1, 'unit' => 'buah'],
            ['name' => 'melon', 'category' => 1, 'unit' => 'buah'],
        ];

        foreach ($items as $item) {
            WarehouseItem::create([
                'name' => $item['name'],
                'warehouse_category_id' => $item['category'],
                'unit' => $item['unit'],
                'stock' => 0,
            ]);
        }
    }
}
