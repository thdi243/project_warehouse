<?php

namespace App\Models\Wrm\StockOpname;

use App\Models\Wrm\MasterBarangModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WrmSoSummariesModel extends Model
{
    use HasFactory;

    protected $table = 'wrm_so_summaries';

    protected $fillable = [
        'so_id',
        'barang_id',
        'no_spb',
        'pallet',
        'jenis_data',
        'qty_fisik',
        'qty_sistem',
        'selisih',
        'status',
        'keterangan',
        'loc_id',
    ];

    public function so()
    {
        return $this->belongsTo(WrmSoModel::class, 'so_id');
    }

    public function barang()
    {
        return $this->belongsTo(MasterBarangModel::class, 'barang_id');
    }

    public function bin()
    {
        return $this->belongsTo(\App\Models\Wrm\MasterBinModel::class, 'loc_id');
    }

    public function location()
    {
        return $this->hasOneThrough(
            \App\Models\Wrm\MasterLocationModel::class,
            \App\Models\Wrm\MasterBinModel::class,
            'id',
            'id',
            'loc_id',
            'loc_id'
        );
    }
}
