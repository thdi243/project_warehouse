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
        'no_spb',
        'pallet_id',
        'group',
        'qty',
        'incoming_date',
        'supplier',
        'status',
        'gudang',
        'loc',
        'catatan',
        'expired_date',
        'transaksi',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'incoming_date' => 'date:Y-m-d',
        'expired_date'  => 'date:Y-m-d',
    ];

    // Relationships

    // relasi ke master barang
    public function barang()
    {
        return $this->belongsTo(MasterBarangModel::class, 'barang_id');
    }

    public function group()
    {
        return $this->belongsTo(GroupStockModel::class, 'group');
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
