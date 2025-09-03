<?php

namespace App\Models\Tkbm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TkbmHargaProdukModel extends Model
{
    use HasFactory;

    protected $table = 'harga_produk_tkbm';

    protected $fillable = [
        'harga_terpal',
        'harga_slipsheet',
        'harga_pallet',
    ];
}
