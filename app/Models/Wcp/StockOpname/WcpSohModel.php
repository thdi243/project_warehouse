<?php

namespace App\Models\Wcp\StockOpname;

use App\Models\User;
use App\Models\Wcp\WcpMasterBarangModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WcpSohModel extends Model
{
    use HasFactory;

    protected $table = 'wcp_soh';

    protected $fillable = [
        'barang_id',
        'user_id',
        'qty_soh',
        'qty_unrest',
        'qty_qi',
        'qty_block',
        'last_updated',
    ];

    public function barang()
    {
        return $this->belongsTo(WcpMasterBarangModel::class, 'barang_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
