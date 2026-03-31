<?php

namespace App\Models\Wrm\Inventory;

use App\Models\Wrm\MasterBarangModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockTransfer extends Model
{
    use HasFactory;

    protected $table = 'wrm_stock_transfers';

    protected $fillable = [
        'tgl_gr',
        'no_reservasi',
        'tgl_reservasi',
        'created_by',
        'updated_by',
    ];

    public function details()
    {
        return $this->hasMany(StockTransferDetail::class, 'transfer_id');
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }
}
