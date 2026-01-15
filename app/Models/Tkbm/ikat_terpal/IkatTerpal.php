<?php

namespace App\Models\Tkbm\ikat_terpal;

use Illuminate\Database\Eloquent\Model;

class IkatTerpal extends Model
{
    protected $table = 'tkbm_ikat_terpal';

    protected $fillable = [
        'tanggal',
        'produk_id',
        'fee_id',
        'user_id',
        'qty_pallet',
        'jml_buruh',
        'subtotal_barang',
        'total_fee',
        'catatan',
    ];

    public function produk()
    {
        return $this->belongsTo(ProdukIkatTerpal::class, 'produk_id');
    }

    public function fee()
    {
        return $this->belongsTo(FeeIkatTerpal::class, 'fee_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}
