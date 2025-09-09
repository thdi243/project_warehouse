<?php

namespace App\Models\P2h;

use Illuminate\Database\Eloquent\Model;

class P2HPalletMoverModel extends Model
{
    protected $table = 'p2h_pallet';
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
    ];

    public function calculateKelayakan()
    {
        $group1 = [
            'cek_baterai',
            'cek_fork',
            'kondisi_body_kebersihan',
            'lampu_kiri',
            'lampu_kanan',
            'lampu_sorot',
            'lampu_sign_depan_kanan',
            'lampu_sign_depan_kiri',
            'kipas_belakang',
        ];

        $group2 = [
            'rantai_lift',
            'sistem_hidrolik',
            'kondisi_axle',
            'sistem_kemudi',
            'panel_display',
        ];

        $group3 = [
            'air_aki',
            'klakson',
            'buzzer_mundur',
            'kaca_spion',
            'kondisi_ban',
            'fungsi_rem',
        ];

        $score = 0;

        // Hitung kontribusi berdasarkan nilai "1"
        $score += collect($group1)->filter(fn($field) => $this->$field == 1)->count() / count($group1) * 20;
        $score += collect($group2)->filter(fn($field) => $this->$field == 1)->count() / count($group2) * 50;
        $score += collect($group3)->filter(fn($field) => $this->$field == 1)->count() / count($group3) * 30;

        $score = round($score, 2);

        if ($score > 80) {
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
}
