<?php

namespace App\Models\Wrm\Inventory;

use App\Models\User;
use App\Models\Wrm\MasterBarangModel;
use App\Models\Wrm\MasterLocationModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class StockInboundDetail extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::saved(function ($model) {
            Cache::store('redis')->forget('wrm_stock_inbound_detail_all_array');
        });

        static::deleted(function ($model) {
            Cache::store('redis')->forget('wrm_stock_inbound_detail_all_array');
        });
    }

    public function newEloquentBuilder($query)
    {
        return new class($query) extends Builder {
            public function update(array $values)
            {
                Cache::store('redis')->forget('wrm_stock_inbound_detail_all_array');
                return parent::update($values);
            }
            public function delete()
            {
                Cache::store('redis')->forget('wrm_stock_inbound_detail_all_array');
                return parent::delete();
            }
        };
    }

    protected $table = 'wrm_stock_inbound_details';

    protected $fillable = [
        'inbound_id',
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

    public function inbound()
    {
        return $this->belongsTo(StockInbound::class, 'inbound_id');
    }

    public function barang()
    {
        return $this->belongsTo(MasterBarangModel::class, 'barang_id');
    }

    public function bin()
    {
        return $this->belongsTo(\App\Models\Wrm\MasterBinModel::class, 'loc_id');
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
