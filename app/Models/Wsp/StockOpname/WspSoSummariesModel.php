<?php

namespace App\Models\Wsp\StockOpname;

use App\Models\Wsp\BarangModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WspSoSummariesModel extends Model
{
    use HasFactory;

    protected $table = 'wsp_so_summaries';

    protected $fillable = [
        'so_id',
        'barang_id',
        'loc_id',
        'qty_fisik',
        'qty_sistem',
        'selisih',
        'status',
        'keterangan',
    ];

    protected $appends = [
        'plant',
        's_loc',
        'detail_loc',
    ];

    public function so()
    {
        return $this->belongsTo(WspSoModel::class, 'so_id');
    }

    public function barang()
    {
        return $this->belongsTo(BarangModel::class, 'barang_id');
    }

    public function location()
    {
        return $this->belongsTo(\App\Models\Wsp\stock_manage\StockLocationModel::class, 'loc_id');
    }

    public function getPlantAttribute()
    {
        return $this->location?->rak?->plant;
    }

    public function getSLocAttribute()
    {
        return $this->location?->rak?->s_loc;
    }

    public function getDetailLocAttribute()
    {
        return $this->location?->rak?->detail_loc;
    }
}
