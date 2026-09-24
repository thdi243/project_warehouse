<?php

namespace App\Models\Kempu;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KempuMainModel extends Model
{
    use HasFactory;

    protected $table = 'kempu_main';

    protected $fillable = [
        'kempu_master_id',
        'id_kempu',
        'current_location',
        'current_status',
        'reused_count',
        'max_reused',
        'condition',
        'last_scanned_at',
        'last_action',
    ];

    protected $casts = [
        'last_scanned_at' => 'datetime',
        'reused_count'    => 'integer',
        'max_reused'      => 'integer',
    ];

    /**
     * Relasi ke master data kempu (identitas aset)
     */
    public function masterKempu()
    {
        return $this->belongsTo(MasterKempuModel::class, 'kempu_master_id');
    }

    /**
     * Cek apakah batas maksimal pemakaian (21x reused) telah tercapai
     */
    public function isMaxReused(): bool
    {
        return ($this->reused_count ?? 0) >= ($this->max_reused ?? 21);
    }
}
