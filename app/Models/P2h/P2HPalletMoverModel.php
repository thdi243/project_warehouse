<?php

namespace App\Models\P2h;

use Illuminate\Database\Eloquent\Model;

class P2HPalletMoverModel extends Model
{
    protected $table = 'p2h_pallet_mover';
    protected $primaryKey = 'id';
    protected $fillable = [
        'tanggal',
        'jenis_p2h',
        'nomor_unit',
        'dept',
        'check_air_accu',
        'check_battery',
        'check_body_unit',
        'check_klakson',
        'check_roda',
        'check_sistem_kemudi',
        'check_kebersihan_unit',
        'check_kunci_pm',
        'check_hydraulic',
        'shift',
        'operator_name',
        'catatan',
        'updated_by',
    ];

    public function calculateKelayakan(): array
    {

        // Pendukung (20%)
        $group1 = [
            'check_body_unit',
            'check_kebersihan_unit',
            'check_kunci_pm',
        ];

        // Sistem utama / kritikal (50%)
        $group2 = [
            'check_battery',
            'check_hydraulic',
            'check_sistem_kemudi',
        ];

        // Safety & operasional (30%)
        $group3 = [
            'check_air_accu',
            'check_klakson',
            'check_roda',
        ];

        $score =
            $this->hitungGroup($group1, 20) +
            $this->hitungGroup($group2, 50) +
            $this->hitungGroup($group3, 30);

        $score = round($score, 2);

        /**
         * STATUS FINAL
         */
        if ($score >= 85) {
            $status = 'Layak';
        } elseif ($score >= 70) {
            $status = 'Perlu Perhatian';
        } else {
            $status = 'Tidak Layak';
        }

        return [
            'persentase' => $score,
            'status' => $status
        ];
    }

    private function hitungGroup(array $fields, float $bobot): float
    {
        if (count($fields) === 0) {
            return 0;
        }

        $valid = collect($fields)
            ->filter(fn($field) => ($this->$field ?? 0) == 1)
            ->count();

        return ($valid / count($fields)) * $bobot;
    }
}
