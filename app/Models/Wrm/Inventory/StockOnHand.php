<?php

namespace App\Models\Wrm\Inventory;

use App\Models\User;
use App\Models\Wrm\MasterBarangModel;
use App\Models\Wrm\MasterBinModel;
use App\Models\Wrm\MasterLocationModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockOnHand extends Model
{
    use HasFactory;

    protected $table = 'wrm_stock_on_hand';

    protected $fillable = [
        'no_spb',
        'incoming_date',
        'expired_date',
        'supplier',
        'barang_id',
        'barcode',
        'pallet_id',
        'group',
        'qty',
        'status',
        'loc_id',
        'catatan',
        'pallet',
        'created_by',
        'updated_by',
    ];

    public function barang()
    {
        return $this->belongsTo(MasterBarangModel::class, 'barang_id');
    }

    public function bin()
    {
        return $this->belongsTo(MasterBinModel::class, 'loc_id');
    }

    public function location()
    {
        return $this->hasOneThrough(
            MasterLocationModel::class,
            \App\Models\Wrm\MasterBinModel::class,
            'id',
            'id',
            'loc_id',
            'loc_id'
        );
    }

    public function balance()
    {
        return $this->belongsTo(StockBalance::class, 'barang_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
