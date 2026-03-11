<?php

namespace App\Models\Wrm\StockGula;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TempUploadModel extends Model
{
    use HasFactory;

    protected $table = 'wrm_stock_gula_temp_upload';

    protected $fillable = [
        'barcode',
        'no_spb',
        'mid',
        'pallet_id',
        'qty',
        'group',
        'status',
        'incoming_date',
        'supplier',
        'pallet',
        'gudang',
        'bin',
        'catatan',
        'created_by',
        'updated_by',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
