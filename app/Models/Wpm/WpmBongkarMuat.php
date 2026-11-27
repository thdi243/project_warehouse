<?php

namespace App\Models\Wpm;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WpmBongkarMuat extends Model
{
    use HasFactory;

    protected $table = 'wpm_bongkar_muats';

    protected $fillable = [
        'tipe',
        'tanggal',
        'no_dokumen',
        'no_spkb',
        'no_ecp',
        'sloc',
        'supplier_vendor',
        'no_segel_bas',
        'jumlah_tkbm',
        'no_mobil',
        'driver_name',
        'gate',
        'jenis_pengiriman',
        'jam_serah_terima_qc',
        'jam_release_qc',
        'jam_muat',
        'jam_selesai',
        'no_kontainer',
        'forklift_driver_id',
        'checker_id',
        'status',
        'approved_at',
        'driver_approved_at',
        'qc_user_id',
        'qc_approved_at',
        'qc_signature',
        'checker_signature',
        'driver_signature',
        'admin_signature',
        'foreman_signature',
        'verified_by',
        'verified_at',
        'verified_note',
        'verified_signature',
        'inspection_data',
        'catatan',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'tanggal' => 'date:Y-m-d',
        'approved_at' => 'datetime',
        'driver_approved_at' => 'datetime',
        'qc_approved_at' => 'datetime',
        'verified_at' => 'datetime',
        'inspection_data' => 'array',
    ];

    public function details()
    {
        return $this->hasMany(WpmBongkarMuatDetail::class, 'wpm_bongkar_muat_id');
    }

    public function forkliftDriver()
    {
        return $this->belongsTo(User::class, 'forklift_driver_id');
    }

    public function checker()
    {
        return $this->belongsTo(User::class, 'checker_id');
    }

    public function qcUser()
    {
        return $this->belongsTo(User::class, 'qc_user_id');
    }

    public function verificator()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
