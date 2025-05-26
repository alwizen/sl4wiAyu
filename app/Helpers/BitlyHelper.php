<?php
namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class BitlyHelper
{
    public static function shorten(string $longUrl): ?string
    {
        // Validasi token terlebih dahulu
        $bitlyToken = env('BITLY_TOKEN');
        if (empty($bitlyToken)) {
            Log::warning('Bitly token not configured');
            return null;
        }

        // Cek cache dulu untuk menghindari duplicate API calls
        $cacheKey = 'bitly_' . md5($longUrl);
        $cachedUrl = Cache::get($cacheKey);
        if ($cachedUrl) {
            return $cachedUrl;
        }

        try {
            $response = Http::timeout(10) // Tambah timeout
            ->withToken($bitlyToken)
                ->post('https://api-ssl.bitly.com/v4/shorten', [
                    'long_url' => $longUrl,
                    'domain' => 'bit.ly'
                ]);

            if ($response->successful()) {
                $shortUrl = $response->json('link'); // Lebih aman daripada ['link']

                // Cache hasil selama 24 jam
                Cache::put($cacheKey, $shortUrl, 86400);

                return $shortUrl;
            }

            // Log error untuk debugging
            $errorData = $response->json();
            Log::warning('Bitly API failed', [
                'status' => $response->status(),
                'error' => $errorData['message'] ?? 'Unknown error',
                'url' => $longUrl
            ]);

        } catch (\Exception $e) {
            Log::error('Bitly API exception', [
                'error' => $e->getMessage(),
                'url' => $longUrl
            ]);
        }

        return null;
    }

    /**
     * Method tambahan untuk cek quota (opsional)
     */
    public static function checkQuota(): ?array
    {
        $bitlyToken = env('BITLY_TOKEN');
        if (empty($bitlyToken)) {
            return null;
        }

        try {
            $response = Http::timeout(10)
                ->withToken($bitlyToken)
                ->get('https://api-ssl.bitly.com/v4/user');

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            Log::error('Bitly quota check failed: ' . $e->getMessage());
        }

        return null;
    }
}
