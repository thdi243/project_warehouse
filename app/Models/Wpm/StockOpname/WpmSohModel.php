<?php

namespace App\Models\Wpm\StockOpname;

use App\Models\User;
use App\Models\Wpm\WpmMasterBarangModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WpmSohModel extends Model
{
    use HasFactory;

    protected $table = 'wpm_soh';

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
        return $this->belongsTo(WpmMasterBarangModel::class, 'barang_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
