<?php

namespace App\Models\Wsp\StockOpname;

use App\Models\User;
use App\Models\Wsp\BarangModel;
use App\Models\Wsp\stock_manage\StockLocationModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WspSohModel extends Model
{
    use HasFactory;

    protected $table = 'wsp_soh';

    protected $fillable = [
        'barang_id',
        'loc_id',
        'jenis_so',
        'user_id',
        'qty_soh',
        'qty_unrest',
        'qty_qi',
        'qty_block',
        'last_updated',
    ];

    public function barang()
    {
        return $this->belongsTo(BarangModel::class, 'barang_id');
    }

    public function location()
    {
        return $this->belongsTo(StockLocationModel::class, 'loc_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
