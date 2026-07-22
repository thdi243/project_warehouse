<?php

namespace App\Models\Wcp\StockOpname;

use App\Models\User;
use App\Models\Wcp\StockOpname\WcpSoApprovalModel;
use App\Models\Wcp\StockOpname\WcpSoDetailModel;
use App\Models\Wcp\StockOpname\WcpSoSummariesModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WcpSoModel extends Model
{
    use HasFactory;

    protected $table = 'wcp_so';

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
        return $this->hasMany(WcpSoDetailModel::class, 'so_id');
    }

    public function summaries()
    {
        return $this->hasMany(WcpSoSummariesModel::class, 'so_id');
    }

    public function approvals()
    {
        return $this->hasMany(WcpSoApprovalModel::class, 'so_id');
    }
}
