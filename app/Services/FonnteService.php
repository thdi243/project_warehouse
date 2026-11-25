<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
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
     * Lempar pengiriman WhatsApp ke Laravel Queue (asynchronous) dengan Global Sequential Pacing.
     * Mengatur jeda otomatis antar pesan sekitar 2 menit (120 detik) di seluruh sistem secara terkoordinasi
     * sehingga tidak ada dua pesan yang terkirim berdekatan apalagi pada menit yang sama, meniru perilaku manusia.
     *
     * @param string $target Nomor penerima
     * @param string $message Isi pesan
     * @param int $minGapSeconds Jeda minimal antar pesan (default 120 detik / 2 menit)
     * @param array $extraParams Parameter Fonnte tambahan
     * @param string|null $logContext Keterangan untuk log
     * @param int $maxGapSeconds Jeda maksimal antar pesan (default 140 detik / ~2 menit)
     * @return array
     */
    public function queueMessage(string $target, string $message, int $minGapSeconds = 120, array $extraParams = [], ?string $logContext = null, int $maxGapSeconds = 140): array
    {
        $cleanTarget = preg_replace('/[^0-9]/', '', $target);
        if (empty($cleanTarget)) {
            Log::warning("[FonnteService] Target nomor telepon kosong, pengiriman dibatalkan.");
            return ['status' => false, 'message' => 'Nomor telepon tujuan tidak valid.'];
        }

        $now = now();
        $cacheKey = 'fonnte_last_scheduled_dispatch_at';
        $lastScheduledStr = null;

        try {
            $lastScheduledStr = Cache::get($cacheKey);
        } catch (\Throwable $e) {
            Log::warning("[FonnteService] Gagal membaca cache antrian: " . $e->getMessage());
        }

        // Jeda acak manusiawi sekitar 2 menit (120 detik) antar pesan agar sangat aman dari deteksi spam WhatsApp
        $minGap = max(90, $minGapSeconds);
        $maxGap = max($minGap, $maxGapSeconds);
        $randomGap = rand($minGap, $maxGap); // Sekitar 2 menit (120 detik)

        if ($lastScheduledStr) {
            try {
                $lastScheduled = Carbon::parse($lastScheduledStr);
                if ($lastScheduled->gt($now)) {
                    // Masih ada antrian pesan di depan, jadwalkan ~2 menit (120s) setelah pesan terakhir
                    $scheduledTime = $lastScheduled->copy()->addSeconds($randomGap);
                } else {
                    // Antrian kosong / pesan sebelumnya sudah lewat, beri jeda awal 10 s/d 20 detik
                    $scheduledTime = $now->copy()->addSeconds(rand(10, 20));
                }
            } catch (\Throwable $e) {
                $scheduledTime = $now->copy()->addSeconds(rand(10, 20));
            }
        } else {
            $scheduledTime = $now->copy()->addSeconds(rand(10, 20));
        }

        // Simpan timestamp pengiriman terjadwal terakhir di cache (berlaku 2 jam)
        try {
            Cache::put($cacheKey, $scheduledTime->toDateTimeString(), 7200);
        } catch (\Throwable $e) {
            Log::warning("[FonnteService] Gagal menyimpan cache antrian: " . $e->getMessage());
        }

        // Fonnte typing delay: 8-15 detik (meniru durasi supir/admin mengetik di WhatsApp)
        if (!isset($extraParams['delay'])) {
            $extraParams['delay'] = rand(8, 15);
        }

        try {
            $job = (new \App\Jobs\SendWhatsAppNotificationJob($cleanTarget, $message, $extraParams, $logContext))
                ->delay($scheduledTime);
            dispatch($job);

            $delaySeconds = $now->diffInSeconds($scheduledTime);
            $delayMinutes = round($delaySeconds / 60, 1);

            Log::info("[FonnteService] Pesan WA dijadwalkan ke queue (Jeda: {$delaySeconds}s / ~{$delayMinutes}m)", [
                'target' => $cleanTarget,
                'scheduled_at' => $scheduledTime->format('H:i:s'),
                'delay_seconds' => $delaySeconds,
                'context' => $logContext,
            ]);

            return [
                'status' => true,
                'queued' => true,
                'scheduled_at' => $scheduledTime->toDateTimeString(),
                'delay_seconds' => $delaySeconds,
                'message' => "Pesan berhasil dijadwalkan ke antrian (jeda ~{$delayMinutes} menit)."
            ];
        } catch (\Throwable $e) {
            Log::warning("[FonnteService] Queue dispatch gagal, fallback ke pengiriman langsung (direct): " . $e->getMessage());
            return $this->sendMessage($cleanTarget, $message, $extraParams);
        }
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
            // Berikan natural typing delay acak (8 s/d 15 detik) jika belum ditentukan
            // Parameter 'delay' pada Fonnte API berguna untuk meniru jeda mengetik manusia agar tidak terdeteksi bot spam
            if (!isset($extraParams['delay'])) {
                $extraParams['delay'] = rand(8, 15);
            }

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

            $filePath = $extraParams['file'] ?? null;
            $hasFile = !empty($filePath) && file_exists($filePath);
            $timeout = $hasFile ? max((int)config('services.fonnte.timeout', 25), 60) : (int)config('services.fonnte.timeout', 25);
            $connectTimeout = (int)config('services.fonnte.connect_timeout', 10);

            // Handle file attachment jika ada file lokal yang valid
            if ($hasFile) {
                $filename = $extraParams['filename'] ?? basename($filePath);
                unset($payload['file']);
                unset($payload['url']); // Pastikan 'url' tidak dikirimkan saat file binary dilampirkan agar Fonnte tidak mencoba fetch ke internal IP (yang menyebabkan timeout)

                $response = Http::withHeaders([
                    'Authorization' => $this->token,
                ])
                    ->withOptions($options)
                    ->timeout($timeout)
                    ->connectTimeout($connectTimeout)
                    ->attach('file', file_get_contents($filePath), $filename)
                    ->post($this->url, $payload);
            } else {
                // Cegah Fonnte hang jika ada parameter 'url' yang mengarah ke internal/private IP
                if (!empty($payload['url'])) {
                    $urlHost = parse_url($payload['url'], PHP_URL_HOST);
                    if ($urlHost && (
                        in_array($urlHost, ['localhost', '127.0.0.1'])
                        || str_starts_with($urlHost, '10.')
                        || str_starts_with($urlHost, '192.168.')
                        || preg_match('/^172\.(1[6-9]|2[0-9]|3[0-1])\./', $urlHost)
                    )) {
                        Log::warning("[FonnteService] URL lampiran '{$payload['url']}' merupakan private/local IP yang tidak dapat diakses Fonnte dari internet. Parameter 'url' dihapus agar tidak timeout.");
                        unset($payload['url']);
                    }
                }

                $response = Http::withHeaders([
                    'Authorization' => $this->token,
                ])
                    ->withOptions($options)
                    ->timeout($timeout)
                    ->connectTimeout($connectTimeout)
                    ->asForm()
                    ->post($this->url, $payload);
            }

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
