<?php

namespace App\Jobs;

use App\Services\FonnteService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendWhatsAppNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Jumlah percobaan maksimal jika gagal.
     */
    public int $tries = 3;

    /**
     * Waktu jeda (detik) sebelum retry jika terjadi kegagalan.
     */
    public array $backoff = [15, 60];

    protected string $target;
    protected string $message;
    protected array $extraParams;
    protected ?string $logContext;

    /**
     * Create a new job instance.
     *
     * @param string $target Nomor tujuan WhatsApp
     * @param string $message Isi pesan WhatsApp
     * @param array $extraParams Parameter tambahan untuk Fonnte API
     * @param string|null $logContext Keterangan konteks log (misal: "Panggilan Dock Truk B 1234 BAS")
     */
    public function __construct(string $target, string $message, array $extraParams = [], ?string $logContext = null)
    {
        $this->target = $target;
        $this->message = $message;
        $this->extraParams = $extraParams;
        $this->logContext = $logContext;
    }

    /**
     * Execute the job.
     */
    public function handle(FonnteService $fonnteService): void
    {
        $cleanTarget = preg_replace('/[^0-9@.\-_]/', '', $this->target);
        if (empty($cleanTarget)) {
            Log::warning("[SendWhatsAppNotificationJob] Target nomor telepon kosong, pengiriman dibatalkan.");
            return;
        }

        $contextStr = $this->logContext ? " [{$this->logContext}]" : "";
        Log::info("[SendWhatsAppNotificationJob] Memproses pengiriman WA ke {$cleanTarget}{$contextStr}");

        $result = $fonnteService->sendMessage($cleanTarget, $this->message, $this->extraParams);

        if (empty($result['status'])) {
            $errMsg = $result['message'] ?? 'Unknown error';
            Log::error("[SendWhatsAppNotificationJob] Gagal kirim WA ke {$cleanTarget}{$contextStr}: {$errMsg}");

            // Lempar Exception agar Laravel Queue retry sesuai $tries dan $backoff
            throw new \Exception("Gagal mengirim WhatsApp ke {$cleanTarget}: {$errMsg}");
        }

        Log::info("[SendWhatsAppNotificationJob] Sukses kirim WA ke {$cleanTarget}{$contextStr}");
    }
}
