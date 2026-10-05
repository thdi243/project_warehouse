<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FonnteService
{
    protected ?string $token;
    protected string $url;

    public function __construct()
    {
        $this->token = config('services.fonnte.token');
        $this->url = config('services.fonnte.url', 'https://api.fonnte.com/send');
    }

    /**
     * Kirim pesan teks WhatsApp melalui Fonnte
     *
     * @param string $target Nomor penerima (contoh: '081234567890' atau format internasional '6281234567890')
     * @param string $message Isi pesan
     * @param array $extraParams Parameter tambahan (seperti 'url', 'filename', 'schedule', 'delay', 'countryCode')
     * @return array
     */
    public function sendMessage(string $target, string $message, array $extraParams = []): array
    {
        if (empty($this->token)) {
            Log::error('[FonnteService] Fonnte token belum dikonfigurasi di file .env');
            return [
                'status' => false,
                'message' => 'Fonnte token belum dikonfigurasi.',
            ];
        }

        try {
            $payload = array_merge([
                'target' => $target,
                'message' => $message,
                'countryCode' => '62',
            ], $extraParams);

            $response = Http::withHeaders([
                'Authorization' => $this->token,
            ])->asForm()->post($this->url, $payload);

            $data = $response->json();

            if ($response->successful()) {
                Log::info('[FonnteService] Pesan berhasil dikirim', [
                    'target' => $target,
                    'response' => $data,
                ]);

                return [
                    'status' => true,
                    'data' => $data,
                ];
            }

            Log::error('[FonnteService] Gagal mengirim pesan', [
                'target' => $target,
                'response' => $data ?? $response->body(),
                'status_code' => $response->status(),
            ]);

            return [
                'status' => false,
                'message' => $data['reason'] ?? 'Gagal mengirim pesan melalui Fonnte.',
                'raw' => $data ?? $response->body(),
            ];
        } catch (\Throwable $e) {
            Log::error('[FonnteService] Terjadi kesalahan saat request ke Fonnte: ' . $e->getMessage());

            return [
                'status' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}
