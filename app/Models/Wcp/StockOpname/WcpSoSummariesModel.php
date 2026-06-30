<?php

namespace App\Models\Wcp\StockOpname;

use App\Models\Wcp\WcpMasterBarangModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WcpSoSummariesModel extends Model
{
    use HasFactory;

    protected $table = 'wcp_so_summaries';

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
        return $this->belongsTo(WcpSoModel::class, 'so_id');
    }

    public function barang()
    {
        return $this->belongsTo(WcpMasterBarangModel::class, 'barang_id');
    }
}
