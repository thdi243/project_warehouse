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
        'has_barcode',
        'has_rfid',
        'has_nti',
        'no_po',
        'foto_1',
        'foto_2',
        'foto_3',
        'foto_4',
        'last_scanned_at',
        'last_action',
    ];

    protected $casts = [
        'last_scanned_at' => 'datetime',
        'reused_count'    => 'integer',
        'max_reused'      => 'integer',
        'has_barcode'     => 'boolean',
        'has_rfid'        => 'boolean',
        'has_nti'       => 'boolean',
    ];

    protected $appends = [
        'photo_urls',
    ];

    public function getPhotoUrlsAttribute(): array
    {
        return [
            'foto_1' => $this->foto_1 ? asset('storage/' . $this->foto_1) : null,
            'foto_2' => $this->foto_2 ? asset('storage/' . $this->foto_2) : null,
            'foto_3' => $this->foto_3 ? asset('storage/' . $this->foto_3) : null,
            'foto_4' => $this->foto_4 ? asset('storage/' . $this->foto_4) : null,
        ];
    }

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
