<?php

namespace App\Models\Kempu;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KempuCycleFillingModel extends Model
{
    use HasFactory;

    protected $table = 'kempu_cycle_fillings';

    protected $fillable = [
        'kempu_master_id',
        'id_kempu',
        'reused_count',
        'has_barcode',
        'has_rfid',
        'has_nti',
        'no_po',
        'foto_1',
        'foto_2',
        'foto_3',
        'foto_4',
        'filled_at',
        'created_by',
    ];

    protected $casts = [
        'reused_count' => 'integer',
        'has_barcode'  => 'boolean',
        'has_rfid'     => 'boolean',
        'has_nti'      => 'boolean',
        'filled_at'    => 'datetime',
    ];

    public function masterKempu()
    {
        return $this->belongsTo(MasterKempuModel::class, 'kempu_master_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
