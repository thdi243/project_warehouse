<?php

namespace App\Models\Wrm\Inventory;

use App\Models\User;
use App\Models\Wrm\MasterBarangModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockInbound extends Model
{
    use HasFactory;

    protected $table = 'wrm_stock_inbound';

    protected $fillable = [
        'no_spb',
        'incoming_date',
        'supplier',
        'created_by',
        'updated_by',
    ];

    public function details()
    {
        return $this->hasMany(StockInboundDetail::class, 'inbound_id');
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
