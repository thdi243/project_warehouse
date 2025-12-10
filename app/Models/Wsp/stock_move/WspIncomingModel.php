<?php

namespace App\Models\Wsp\stock_move;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class WspIncomingModel extends Model
{
    protected $table = 'wsp_incoming';

    protected $fillable = [
        'user_id',
        'request_date',
        'pr_number',
        'mid',
        'nama_barang',
        'text',
        'requisitio',
        'recipient',
        'cc_email',
        'po_number',
        'po_date',
        'gr_qty',
        'gr_date',
        'material_doc',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
