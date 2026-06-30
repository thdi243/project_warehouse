<?php

namespace App\Models\Wpm\StockOpname;

use App\Models\Wpm\WpmMasterBarangModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WpmSoSummariesModel extends Model
{
    use HasFactory;

    protected $table = 'wpm_so_summaries';

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
        return $this->belongsTo(WpmSoModel::class, 'so_id');
    }

    public function barang()
    {
        return $this->belongsTo(WpmMasterBarangModel::class, 'barang_id');
    }
}
