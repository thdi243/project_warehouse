<?php

namespace App\Models\Wpm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WpmBongkarMuatDetail extends Model
{
    use HasFactory;

    protected $table = 'wpm_bongkar_muat_details';

    protected $fillable = [
        'wpm_bongkar_muat_id',
        'material_id',
        'mid',
        'nama_barang',
        'uom',
        'qty',
        'batch_number',
        'jenis',
        'keterangan',
    ];

    public function bongkarMuat()
    {
        return $this->belongsTo(WpmBongkarMuat::class, 'wpm_bongkar_muat_id');
    }

    public function material()
    {
        return $this->belongsTo(WpmMasterBarangModel::class, 'material_id');
    }
}
