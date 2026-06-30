<?php

namespace App\Models\Wcp\StockOpname;

use App\Models\Wcp\StockOpname\WcpSoModel;
use App\Models\Wcp\WcpMasterBarangModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WcpSoDetailModel extends Model
{
    use HasFactory;

    protected $table = 'wcp_so_detail';

    protected $fillable = [
        'so_id',
        'barang_id',
        'qty_full',
        'qty_receh',
        'created_at',
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
