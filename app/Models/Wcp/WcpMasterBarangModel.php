<?php

namespace App\Models\Wcp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WcpMasterBarangModel extends Model
{
    use HasFactory;

    protected $table = 'wcp_master_barang';

    protected $fillable = [
        'mid',
        'nama_barang',
        'uom',
        'qty_pallet',
    ];
}
