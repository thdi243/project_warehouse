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
        'qty_fisik',
        'qty_sistem',
        'selisih',
        'status',
        'keterangan',
    ];

    public function so()
    {
        return $this->belongsTo(WspSoModel::class, 'so_id');
    }

    public function barang()
    {
        return $this->belongsTo(BarangModel::class, 'barang_id');
    }
}
