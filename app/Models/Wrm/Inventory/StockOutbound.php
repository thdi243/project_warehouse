<?php

namespace App\Models\Wrm\Inventory;

use App\Models\User;
use App\Models\Wrm\MasterBarangModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockOutbound extends Model
{
    use HasFactory;

    protected $table = 'wrm_stock_outbound';

    protected $fillable = [
        'no_spb',
        'incoming_date',
        'supplier',
        'issued_date',
        'expired_date',
        'created_by',
        'updated_by',
    ];

    public function details()
    {
        return $this->hasMany(StockOutboundDetail::class, 'outbound_id');
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
