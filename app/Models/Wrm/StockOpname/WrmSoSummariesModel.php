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
    ];

    public function so()
    {
        return $this->belongsTo(WrmSoModel::class, 'so_id');
    }

    public function barang()
    {
        return $this->belongsTo(MasterBarangModel::class, 'barang_id');
    }
}
