<?php

namespace App\Console\Commands;

use App\Models\Kempu\KempuMainModel;
use App\Models\Kempu\KempuTrackingHistoryModel;
use App\Models\Kempu\MasterKempuModel;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoTransferInPasKempu extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kempu:auto-transfer-in-pas 
                            {--hours=24 : Minimal durasi dalam jam sejak scan Transfer Out di WFG (default: 24 jam / H+1)}
                            {--dry-run : Menampilkan daftar kempu yang akan diupdate tanpa mengubah database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto update status kempu dari WFG_TRANSFER_OUT_TO_PAS menjadi Transfer in From BAS di Warehouse PAS setelah H+1';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $hours = (int) ($this->option('hours') ?? 24);
        $isDryRun = (bool) $this->option('dry-run');
        $threshold = Carbon::now()->subHours($hours);

        $this->info("=== Auto Update Status Kempu WFG -> PAS (H+1) ===");
        $this->line("Batas waktu (Threshold): <= " . $threshold->format('Y-m-d H:i:s') . " (minimal {$hours} jam lalu)");
        if ($isDryRun) {
            $this->warn("[DRY RUN MODE] Tidak ada perubahan yang akan disimpan ke database.");
        }

        // Cari kempu dengan status WFG_TRANSFER_OUT_TO_PAS yang telah melewati H+1 (>= 24 jam)
        $candidates = KempuMainModel::with('masterKempu')
            ->where(function ($query) {
                $query->where('current_status', MasterKempuModel::STATUS_WFG_TRANSFER_OUT_PAS)
                      ->orWhere('current_status', 'Transfer Out to PAS')
                      ->orWhere('current_status', MasterKempuModel::STATUS_IN_TRANSIT_PAS);
            })
            ->where(function ($query) use ($threshold) {
                $query->where('last_scanned_at', '<=', $threshold)
                      ->orWhere(function ($subQuery) use ($threshold) {
                          $subQuery->whereNull('last_scanned_at')
                                   ->where('updated_at', '<=', $threshold);
                      });
            })
            ->get();

        $total = $candidates->count();

        if ($total === 0) {
            $this->info("Tidak ada kempu berstatus Transfer Out to PAS yang memenuhi syarat H+1 saat ini.");
            return Command::SUCCESS;
        }

        $this->line("Ditemukan {$total} kempu yang memenuhi syarat untuk diupdate.");

        if ($isDryRun) {
            $tableData = $candidates->map(function ($item) {
                $scannedAt = $item->last_scanned_at ? $item->last_scanned_at->format('Y-m-d H:i:s') : ($item->updated_at ? $item->updated_at->format('Y-m-d H:i:s') : '-');
                $elapsed = $item->last_scanned_at ? $item->last_scanned_at->diffForHumans(null, true) : ($item->updated_at ? $item->updated_at->diffForHumans(null, true) : '-');
                return [
                    'ID Kempu'       => $item->id_kempu,
                    'Status Asal'    => $item->current_status,
                    'Waktu Scan WFG' => $scannedAt,
                    'Durasi Berlalu' => $elapsed,
                    'Status Baru'    => MasterKempuModel::STATUS_PAS_TRANSFER_IN_BAS,
                    'Lokasi Baru'    => MasterKempuModel::LOC_PAS,
                ];
            });

            $this->table(['ID Kempu', 'Status Asal', 'Waktu Scan WFG', 'Durasi Berlalu', 'Status Baru', 'Lokasi Baru'], $tableData);
            return Command::SUCCESS;
        }

        $successCount = 0;
        $failCount = 0;

        foreach ($candidates as $kempuMain) {
            DB::beginTransaction();
            try {
                $oldStatus = $kempuMain->current_status;
                $scannedAt = $kempuMain->last_scanned_at;
                $masterId  = $kempuMain->kempu_master_id ?? $kempuMain->masterKempu?->id;

                // 1. Update kempu_main
                $kempuMain->update([
                    'current_status'   => MasterKempuModel::STATUS_PAS_TRANSFER_IN_BAS,
                    'current_location' => MasterKempuModel::LOC_PAS,
                    'last_scanned_at'  => Carbon::now(),
                    'last_action'      => 'Transfer in From BAS',
                ]);

                // 2. Tambah record di kempu_tracking_history
                KempuTrackingHistoryModel::create([
                    'kempu_master_id' => $masterId,
                    'id_kempu'        => $kempuMain->id_kempu,
                    'stage'           => 'PAS',
                    'action'          => 'Transfer in From BAS',
                    'action_result'   => 'SUCCESS',
                    'from_location'   => MasterKempuModel::LOC_WFG,
                    'to_location'     => MasterKempuModel::LOC_PAS,
                    'reused_count'    => $kempuMain->reused_count ?? 0,
                    'condition'       => $kempuMain->condition ?? 'OK',
                    'notes'           => 'Auto update status H+1 dari scan Transfer Out to PAS di WFG (Scheduler)',
                    'metadata'        => [
                        'auto_schedule'   => true,
                        'previous_status' => $oldStatus,
                        'scanned_out_at'  => $scannedAt ? $scannedAt->toIso8601String() : null,
                        'processed_at'    => Carbon::now()->toIso8601String(),
                        'hours_threshold' => $hours,
                    ],
                    'created_by'      => null,
                ]);

                DB::commit();
                $successCount++;
                $this->line(" - [BERHASIL] Kempu {$kempuMain->id_kempu} diupdate ke " . MasterKempuModel::STATUS_PAS_TRANSFER_IN_BAS);
            } catch (\Throwable $e) {
                DB::rollBack();
                $failCount++;
                $this->error(" - [GAGAL] Kempu {$kempuMain->id_kempu}: " . $e->getMessage());
                Log::error("Kempu Auto Transfer In PAS Error [{$kempuMain->id_kempu}]: " . $e->getMessage(), [
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        $summary = "Auto update status kempu selesai: {$successCount} berhasil, {$failCount} gagal.";
        $this->info($summary);
        Log::info("Kempu AutoTransferInPas: " . $summary);

        return Command::SUCCESS;
    }
}
