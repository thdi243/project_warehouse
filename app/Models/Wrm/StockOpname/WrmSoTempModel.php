<?php

namespace App\Models\Wrm\StockOpname;

use App\Models\User;
use App\Models\Wrm\MasterBarangModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WrmSoTempModel extends Model
{
    use HasFactory;

    protected $table = 'wrm_so_temp';

    protected $fillable = [
        'soh_id',
        'barang_id',
        'no_spb',
        'qty_full',
        'qty_receh',
        'summary',
        'created_by',
        'tgl_opname',
    ];

    public function barang()
    {
        return $this->belongsTo(MasterBarangModel::class, 'barang_id');
    }

    public function soh()
    {
        return $this->belongsTo(WrmSohModel::class, 'soh_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
