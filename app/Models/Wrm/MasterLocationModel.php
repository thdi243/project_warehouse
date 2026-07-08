<?php

namespace App\Models\Wrm;

use App\Models\Wrm\MasterBarangModel;
use App\Models\Wrm\MasterBinModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class MasterLocationModel extends Model
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

    protected $table = 'wrm_master_location';

    protected $fillable = [
        'plant',
        's_loc',
        'gudang',
        'zona',
        'bin',
        'created_by',
        'updated_by',
    ];

    public function bins()
    {
        return $this->hasMany(MasterBinModel::class, 'loc_id', 'id');
    }

    public function barangs()
    {
        return $this->hasMany(MasterBarangModel::class, 'loc_id', 'id');
    }
}
