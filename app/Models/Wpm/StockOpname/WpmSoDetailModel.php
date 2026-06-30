<?php

namespace App\Models\Wpm\StockOpname;

use App\Models\Wpm\WpmMasterBarangModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WpmSoDetailModel extends Model
{
    use HasFactory;

    protected $table = 'wpm_so_detail';

    protected $fillable = [
        'so_id',
        'barang_id',
        'qty_full',
        'qty_receh',
        'created_at',
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
