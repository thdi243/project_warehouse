<?php

namespace App\Models\Wsp\stock_move;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class WspOutgoingModel extends Model
{
    protected $table = 'wsp_outgoing';

    protected $fillable = [
        'user_id',
        'mid',
        'nama_barang',
        's_loc',
        'unit',
        'material_doc',
        'posting_date',
        'qty',
        'mvt',
        'vendor',
        'batch',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
