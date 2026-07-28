<?php

namespace App\Models\Wsp\StockOpname;

use App\Models\User;
use App\Models\Wsp\BarangModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WspSoTempModel extends Model
{
    use HasFactory;

    protected $table = 'wsp_so_temp';

    protected $fillable = [
        'soh_id',
        'barang_id',
        'loc_id',
        'qty_full',
        'qty_receh',
        'summary',
        'created_by',
        'tgl_opname',
    ];

    public function barang()
    {
        return $this->belongsTo(BarangModel::class, 'barang_id');
    }

    public function soh()
    {
        return $this->belongsTo(WspSohModel::class, 'soh_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
