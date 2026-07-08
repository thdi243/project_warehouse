<?php

namespace App\Models\Wrm;

use App\Models\User;
use App\Models\Wrm\MasterLocationModel;
use App\Models\Wrm\StockGula\StockGulaModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;


class MasterBarangModel extends Model
{
    use HasFactory, SoftDeletes;

    protected static function booted()
    {
        static::saved(function ($model) {
            Cache::store('redis')->forget('wrm_stock_on_hand_all_array');
            Cache::store('redis')->forget('wrm_stock_inbound_detail_all_array');
        });

        static::deleted(function ($model) {
            Cache::store('redis')->forget('wrm_stock_on_hand_all_array');
            Cache::store('redis')->forget('wrm_stock_inbound_detail_all_array');
        });
    }

    public function newEloquentBuilder($query)
    {
        return new class($query) extends Builder {
            public function update(array $values)
            {
                Cache::store('redis')->forget('wrm_stock_on_hand_all_array');
                Cache::store('redis')->forget('wrm_stock_inbound_detail_all_array');
                return parent::update($values);
            }
            public function delete()
            {
                Cache::store('redis')->forget('wrm_stock_on_hand_all_array');
                Cache::store('redis')->forget('wrm_stock_inbound_detail_all_array');
                return parent::delete();
            }
        };
    }

    protected $table = 'wrm_master_barang';

    protected $fillable = [
        'mid',
        'nama_barang',
        'uom',
        // 'loc_id',
        'qty_kg',
        'created_by',
        'updated_by',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function location()
    {
        return $this->belongsTo(MasterLocationModel::class, 'loc_id', 'id');
    }

    // user pembuat
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // user pengupdate
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
