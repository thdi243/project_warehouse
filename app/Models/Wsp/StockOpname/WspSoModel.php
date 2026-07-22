<?php

namespace App\Models\Wsp\StockOpname;

use App\Models\User;
use App\Models\Wsp\StockOpname\WspSoApprovalModel;
use App\Models\Wsp\StockOpname\WspSoDetailModel;
use App\Models\Wsp\StockOpname\WspSoSummariesModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WspSoModel extends Model
{
    use HasFactory;

    protected $table = 'wsp_so';

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
        return $this->hasMany(WspSoDetailModel::class, 'so_id');
    }

    public function summaries()
    {
        return $this->hasMany(WspSoSummariesModel::class, 'so_id');
    }

    public function approvals()
    {
        return $this->hasMany(WspSoApprovalModel::class, 'so_id');
    }
}
