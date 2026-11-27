<?php

namespace App\Models\Wrm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WrmBongkarMuatDetail extends Model
{
    use HasFactory;

    protected $table = 'wrm_bongkar_muat_details';

    protected $fillable = [
        'wrm_bongkar_muat_id',
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
        return $this->belongsTo(WrmBongkarMuat::class, 'wrm_bongkar_muat_id');
    }

    public function material()
    {
        return $this->belongsTo(MasterBarangModel::class, 'material_id');
    }
}
