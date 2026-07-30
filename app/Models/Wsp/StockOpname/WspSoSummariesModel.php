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
        'area_rak',
        'nama_rak',
        'kolom_rak',
        'level_rak',
        'bin_rak',
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

    public function getAreaRakAttribute()
    {
        return $this->location?->rak?->area_rak;
    }

    public function getNamaRakAttribute()
    {
        return $this->location?->rak?->nama_rak;
    }

    public function getKolomRakAttribute()
    {
        return $this->location?->rak?->kolom_rak;
    }

    public function getLevelRakAttribute()
    {
        return $this->location?->rak?->level_rak;
    }

    public function getBinRakAttribute()
    {
        return $this->location?->rak?->box_rak;
    }
}
