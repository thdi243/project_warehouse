<?php

namespace App\Models\Wrm\Inventory;

use App\Models\User;
use App\Models\Wrm\MasterBarangModel;
use App\Models\Wrm\MasterBinModel;
use App\Models\Wrm\MasterLocationModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockOutboundDetail extends Model
{
    use HasFactory;

    protected $table = 'wrm_stock_draft_outbound_details';

    protected $fillable = [
        'outbound_id',
        'no_spb',
        'supplier',
        'barang_id',
        'barcode',
        'pallet_id',
        'incoming_date',
        'group',
        'qty',
        'qty_request',
        'batch_id',
        'status',
        'expired_date',
        'loc_id',
        'catatan',
        'pallet',
        'driver_id',
        'created_by',
        'updated_by',
    ];


    public function outbound()
    {
        return $this->belongsTo(StockOutbound::class, 'outbound_id');
    }

    public function driver()
    {
        return $this->belongsTo(\App\Models\User::class, 'driver_id');
    }

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
            MasterBinModel::class,
            'id',
            'id',
            'loc_id',
            'loc_id'
        );
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
