<?php

namespace App\Models\Wrm\StockOpname;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WrmSoModel extends Model
{
    use HasFactory;

    protected $table = 'wrm_so';

    protected $fillable = [
        'tgl_opname',
        'jenis_so',
        'user_id',
        'status',
        'no_doc',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function details()
    {
        return $this->hasMany(WrmSoDetailModel::class, 'so_id');
    }

    public function summaries()
    {
        return $this->hasMany(WrmSoSummariesModel::class, 'so_id');
    }

    public function approvals()
    {
        return $this->hasMany(WrmSoApprovalModel::class, 'so_id');
    }
}
