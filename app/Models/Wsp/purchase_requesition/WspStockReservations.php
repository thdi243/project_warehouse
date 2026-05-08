<?php

namespace App\Models\Wsp\purchase_requesition;

use App\Models\Wsp\BarangModel;
use Illuminate\Database\Eloquent\Model;

class WspStockReservations extends Model
{
    protected $table = 'wsp_stock_reservations';

    protected $fillable = [
        'mid_barang',
        'qty',
        'type',
        'session_id',
        'user_id',
        'status',
        'reserved_at',
        'expired_at',
        'confirmed_at',
    ];

    protected $casts = [
        'reserved_at' => 'datetime',
        'expired_at' => 'datetime',
        'confirmed_at' => 'datetime',
    ];

    public function barang()
    {
        return $this->belongsTo(BarangModel::class, 'mid_barang', 'mid_barang');
    }
}
