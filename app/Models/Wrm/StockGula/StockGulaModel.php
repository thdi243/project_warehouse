<?php

namespace App\Models\Wrm\StockGula;

use App\Models\User;
use App\Models\Wrm\MasterBarangModel;
use App\Models\Wrm\MasterLocationModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'loc_id',
        'catatan',
        'issued_date',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'incoming_date' => 'date:d-m-Y',
    ];

    // Relationships
    public function barang()
    {
        return $this->belongsTo(MasterBarangModel::class, 'barang_id');
    }

    public function location()
    {
        return $this->belongsTo(MasterLocationModel::class, 'loc_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
