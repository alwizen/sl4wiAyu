<?php

namespace Database\Seeders;

use App\Models\Recipient;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RecipientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $recipients = [
            ['code' => '69956759', 'name' => 'SDIT HARAPAN BANGSA', 'total_recipients' => 180],
            ['code' => '60713551', 'name' => 'MIS NAHDLATUL ULAMA 01 PENDAWA', 'total_recipients' => 151],
            ['code' => '20360191', 'name' => 'TK AISYIYAH BUSTANUL ATHFAL JATIMULYA', 'total_recipients' => 42],
            ['code' => '20361829', 'name' => 'SD NEGERI JATIMULYA 01', 'total_recipients' => 171],
            ['code' => '60713554', 'name' => 'MIS ISLAMIYAH JATIMULYA', 'total_recipients' => 136],
            ['code' => '20363231', 'name' => 'MAN 1 TEGAL', 'total_recipients' => 1434],
            ['code' => '20325233', 'name' => 'SD NEGERI LEBAKGOWAH 02', 'total_recipients' => 269],
            ['code' => '20325235', 'name' => 'SD NEGERI LEBAKGOWAH 01', 'total_recipients' => 87],
            ['code' => '20364683', 'name' => 'MTSS NAHDLATUL ULAMA 1 LEBAKSIU', 'total_recipients' => 205],
            ['code' => '20325386', 'name' => 'SD NEGERI SLAWI KULON 07', 'total_recipients' => 112],
            ['code' => '20325507', 'name' => 'SD NEGERI TEGALANDONG 01', 'total_recipients' => 187],
            ['code' => '20325328', 'name' => 'SMP NEGERI 2 LEBAKSIU', 'total_recipients' => 852],
        ];

        foreach ($recipients as $recipient) {
            Recipient::create([
                'code' => $recipient['code'],
                'name' => $recipient['name'],
                'address' => 'Tegal',
                'phone' => '0',
                'total_recipients' => $recipient['total_recipients'],
            ]);
        }
    }
}
