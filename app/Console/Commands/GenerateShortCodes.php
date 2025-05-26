<?php

namespace App\Console\Commands;

use App\Models\Delivery;
use Illuminate\Console\Command;

class GenerateShortCodes extends Command
{
    protected $signature = 'deliveries:generate-short-codes';
    protected $description = 'Generate short codes for existing deliveries';

    public function handle()
    {
        $deliveries = Delivery::whereNull('short_code')->get();
        $count = 0;

        foreach ($deliveries as $delivery) {
            do {
                $shortCode = $this->generateShortCode();
            } while (Delivery::where('short_code', $shortCode)->exists());

            $delivery->short_code = $shortCode;
            $delivery->save();
            $count++;
        }

        $this->info("Generated short codes for {$count} deliveries");
    }

    private function generateShortCode(): string
    {
        $characters = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz';
        return substr(str_shuffle($characters), 0, 6);
    }
}
