<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class HubClient
{
    public static function submitIntake(array $payload): array
    {
        $base = rtrim(config('services.hub.base_url'), '/');
        $code = config('services.hub.sppg_code');
        $url  = "{$base}/api/v1/sppgs/{$code}/intakes";

        $resp = Http::timeout(15)
            ->retry(2, 1000)
            ->acceptJson()     // <— minta JSON
            ->asJson()         // <— kirim JSON
            ->withToken(config('services.hub.api_key'))
            ->withHeaders([
                'X-Idempotency-Key' => $payload['po_number'] ?? (string) Str::uuid(),
            ])
            ->post($url, $payload);

        // Lemparkan error non-2xx/3xx
        $resp->throw();

        // Coba parse JSON
        $data = $resp->json();

        // Fallback kalau header/format tak persis JSON
        if (!is_array($data) || $data === []) {
            $data = json_decode($resp->body(), true);
        }

        if (!is_array($data)) {
            // Log supaya gampang jejak
            \Log::error('Unexpected response from Hub', [
                'status'  => $resp->status(),
                'headers' => $resp->headers(),
                'body'    => $resp->body(),
            ]);
            throw new \RuntimeException('Unexpected non-JSON response from Hub: ' . $resp->status());
        }

        return $data;
    }
}
