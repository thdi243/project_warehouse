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

            $options = [
                'curl' => [
                    CURLOPT_IPRESOLVE   => CURL_IPRESOLVE_V4,
                    CURLOPT_TCP_NODELAY => 1,
                ],
                'verify' => false,
            ];

            $proxySetting = config('services.fonnte.proxy');
            if (!empty($proxySetting) && $proxySetting !== 'none') {
                $options['proxy'] = $proxySetting;
            } else {
                // Default: abaikan HTTP_PROXY / HTTPS_PROXY bawaan Docker environment agar request langsung ke api.fonnte.com
                $options['proxy'] = false;
            }

            $timeout = (int)config('services.fonnte.timeout', 25);
            $connectTimeout = (int)config('services.fonnte.connect_timeout', 10);

            $response = Http::withHeaders([
                'Authorization' => $this->token,
            ])
                ->withOptions($options)
                ->timeout($timeout)
                ->connectTimeout($connectTimeout)
                ->asForm()
                ->post($this->url, $payload);

            $data = $response->json();

            if ($response->successful() && !empty($data['status'])) {
                Log::info('[FonnteService] Pesan berhasil dikirim', [
                    'target' => $target,
                    'response' => $data,
                ]);

                return [
                    'status' => true,
                    'data' => $data,
                ];
            }

            $reason = $data['reason'] ?? 'Gagal mengirim pesan melalui Fonnte.';
            if (str_contains(strtolower($reason), 'disconnected device')) {
                $reason = 'Perangkat WhatsApp di Fonnte terputus (disconnected). Harap periksa dan hubungkan ulang (scan QR) di dashboard Fonnte.';
            }

            Log::error('[FonnteService] Gagal mengirim pesan', [
                'target' => $target,
                'response' => $data ?? $response->body(),
                'status_code' => $response->status(),
                'reason' => $reason,
            ]);

            return [
                'status' => false,
                'message' => $reason,
                'raw' => $data ?? $response->body(),
            ];
        } catch (\Throwable $e) {
            $errorMsg = $e->getMessage();
            Log::error('[FonnteService] Terjadi kesalahan saat request ke Fonnte: ' . $errorMsg);

            if (str_contains($errorMsg, 'cURL error 28') || str_contains(strtolower($errorMsg), 'timed out')) {
                $errorMsg = 'Koneksi ke gateway WhatsApp (Fonnte) mengalami batas waktu (timeout). Server Fonnte sedang lambat merespons atau koneksi internet terputus sesaat. Silakan coba kirim ulang beberapa saat lagi.';
            }

            return [
                'status' => false,
                'message' => $errorMsg,
            ];
        }
    }

    /**
     * Cek status koneksi perangkat WhatsApp dan sisa kuota di Fonnte
     */
    public function checkDeviceStatus(): array
    {
        if (empty($this->token)) {
            return ['status' => false, 'message' => 'Fonnte token belum dikonfigurasi.'];
        }

        try {
            $options = [
                'curl' => [
                    CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
                ],
                'verify' => false,
            ];

            $proxySetting = config('services.fonnte.proxy');
            if (!empty($proxySetting) && $proxySetting !== 'none') {
                $options['proxy'] = $proxySetting;
            } else {
                $options['proxy'] = false;
            }

            $response = Http::withHeaders([
                'Authorization' => $this->token,
            ])
                ->withOptions($options)
                ->timeout(10)
                ->connectTimeout(5)
                ->post('https://api.fonnte.com/device');

            return $response->json() ?? ['status' => false, 'message' => 'Gagal membaca status device Fonnte.'];
        } catch (\Throwable $e) {
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }
}
