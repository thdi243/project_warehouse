<?php

namespace App\Models\Wfg;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BongkarMuat extends Model
{
    use HasFactory;

    protected $table = 'wfg_bongkar_muats';

    protected $fillable = [
        'tanggal',
        'no_dokumen',
        'shipment_smu',
        'wavepick_smu',
        'shipment_bas',
        'wavepick_bas',
        'forklift_driver_id',
        'destinasi_id',
        'no_mobil',
        'gate',
        'no_kontainer',
        'no_segel_bas',
        'no_segel_vendor',
        'jumlah_slipsheet',
        'jam_muat',
        'status',
        'checker_id',
        'approved_at',
        'driver_name',
        'driver_approved_at',
        'verified_by',
        'verified_at',
        'verified_note',
        'checker_signature',
        'driver_signature',
        'verified_signature',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'tanggal' => 'date:Y-m-d',
        'approved_at' => 'datetime',
        'driver_approved_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function details()
    {
        return $this->hasMany(BongkarMuatDetail::class, 'bongkar_muat_id');
    }

    public function forkliftDriver()
    {
        return $this->belongsTo(User::class, 'forklift_driver_id');
    }

    public function checker()
    {
        return $this->belongsTo(User::class, 'checker_id');
    }

    public function verificator()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function destinasi()
    {
        return $this->belongsTo(MasterDestinasi::class, 'destinasi_id');
    }
}
