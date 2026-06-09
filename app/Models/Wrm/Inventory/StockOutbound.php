<?php

namespace App\Models\Wrm\Inventory;

use App\Models\User;
use App\Models\Wrm\MasterBarangModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockOutbound extends Model
{
    use HasFactory;

    protected $table = 'wrm_stock_draft_outbound';

    protected $fillable = [
        'no_reservasi',
        'shift',
        'reservasi_date',
        'qty_request',
        'catatan',
        'checklist_kondisi',
        'status_transfer',
        'created_by',
        'updated_by',
    ];

    public function details()
    {
        return $this->hasMany(StockOutboundDetail::class, 'outbound_id');
    }

    public function recalculateQtyRequest()
    {
        $hasBatches = $this->details()->whereNotNull('batch_id')->exists();
        if (!$hasBatches) {
            return;
        }

        $totalRequest = $this->details()
            ->whereNotNull('batch_id')
            ->select('batch_id', 'qty_request')
            ->get()
            ->groupBy('batch_id')
            ->map(function ($group) {
                return $group->first()->qty_request ?? 0;
            })
            ->sum();

        $this->update(['qty_request' => $totalRequest]);
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
