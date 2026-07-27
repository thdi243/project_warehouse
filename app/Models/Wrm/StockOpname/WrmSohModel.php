<?php

namespace App\Models\Wrm\StockOpname;

use App\Models\User;
use App\Models\Wrm\MasterBarangModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WrmSohModel extends Model
{
    use HasFactory;

    protected $table = 'wrm_soh';

    protected $fillable = [
        'barang_id',
        'jenis_so',
        'user_id',
        'jenis_data',
        'no_spb',
        'pallet',
        'loc_id',
        'qty_soh',
        'qty_unrest',
        'qty_qi',
        'qty_block',
        'last_updated',
    ];

    public function barang()
    {
        return $this->belongsTo(MasterBarangModel::class, 'barang_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function bin()
    {
        return $this->belongsTo(\App\Models\Wrm\MasterBinModel::class, 'loc_id');
    }

    public function location()
    {
        return $this->hasOneThrough(
            \App\Models\Wrm\MasterLocationModel::class,
            \App\Models\Wrm\MasterBinModel::class,
            'id',
            'id',
            'loc_id',
            'loc_id'
        );
    }
}
