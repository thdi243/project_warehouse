<?php

namespace App\Models\Tkbm\ikat_terpal;

use Illuminate\Database\Eloquent\Model;

class ProdukIkatTerpal extends Model
{
    protected $table = 'tkbm_produk_ikat_terpal';

    protected $fillable = [
        'harga_pallet',
        'satuan',
        'aktif',
        'keterangan',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
