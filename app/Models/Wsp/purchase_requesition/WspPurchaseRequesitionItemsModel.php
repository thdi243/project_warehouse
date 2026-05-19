<?php

namespace App\Models\Wsp\purchase_requesition;

use App\Models\Wsp\BarangModel;
use Illuminate\Database\Eloquent\Model;
use App\Models\Wsp\stock_manage\StockOnHandWspModel;

class WspPurchaseRequesitionItemsModel extends Model
{
    protected $table = 'wsp_purchase_requesition_items';

    protected $fillable = [
        'pr_id',
        'barang_id',
        'jenis',
        'qty',
        'keterangan',
        'alasan'
    ];

    public function barang()
    {
        return $this->belongsTo(BarangModel::class);
    }

    public function latestStock()
    {
        return $this->hasOne(StockOnHandWspModel::class, 'barang_id', 'barang_id')
            ->latest('last_update');
    }

    public function approval()
    {
        return $this->hasMany(WspPurchaseRequesitionItemApprovalModel::class, 'pr_item_id');
    }
}
