<?php

namespace App\Models\Wsp\purchase_requesition;

use App\Models\Wsp\BarangModel;
use Illuminate\Database\Eloquent\Model;

class WspPurchaseRequesitionItemsModel extends Model
{
    protected $table = 'wsp_purchase_requesition_items';

    protected $fillable = [
        'pr_id',
        'barang_id',
        'qty',
        'keterangan'
    ];

    public function barang()
    {
        return $this->belongsTo(BarangModel::class);
    }
}
