<?php

namespace App\Models\Wcp\StockOpname;

use App\Models\User;
use App\Models\Wcp\WcpMasterBarangModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WcpSoTempNoteModel extends Model
{
    use HasFactory;

    protected $table = 'wcp_so_temp_note';

    protected $fillable = [
        'soh_id',
        'barang_id',
        'catatan',
        'created_by',
        'tgl_opname',
    ];

    public function barang()
    {
        return $this->belongsTo(WcpMasterBarangModel::class, 'barang_id');
    }

    public function soh()
    {
        return $this->belongsTo(WcpSohModel::class, 'soh_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
