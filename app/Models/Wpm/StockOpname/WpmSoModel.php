<?php

namespace App\Models\Wpm\StockOpname;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WpmSoModel extends Model
{
    use HasFactory;

    protected $table = 'wpm_so';

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
        return $this->hasMany(WpmSoDetailModel::class, 'so_id');
    }

    public function summaries()
    {
        return $this->hasMany(WpmSoSummariesModel::class, 'so_id');
    }

    public function approvals()
    {
        return $this->hasMany(WpmSoApprovalModel::class, 'so_id');
    }
}
