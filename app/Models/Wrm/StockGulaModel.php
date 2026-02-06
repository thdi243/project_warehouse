<?php

namespace App\Models\Wrm;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StockGulaModel extends Model
{
    use HasFactory;

    protected $table = 'wrm_stock_gula';

    protected $fillable = [
        'barang_id',
        'tanggal',
        'location',
        'no_spb',
        'qty',
        'incoming_date',
        'supplier',
        'status',
        'gudang',
        'pallet',
        'catatan',
        'expired_date',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'tanggal'       => 'date:Y-m-d',
        'incoming_date' => 'date:Y-m-d',
        'expired_date'  => 'date:Y-m-d',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // relasi ke master barang
    public function barang()
    {
        return $this->belongsTo(MasterBarangModel::class, 'barang_id');
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
