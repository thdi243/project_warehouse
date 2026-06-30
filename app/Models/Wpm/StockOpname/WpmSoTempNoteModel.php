<?php

namespace App\Models\Wpm\StockOpname;

use App\Models\User;
use App\Models\Wpm\WpmMasterBarangModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WpmSoTempNoteModel extends Model
{
    use HasFactory;

    protected $table = 'wpm_so_temp_note';

    protected $fillable = [
        'soh_id',
        'barang_id',
        'catatan',
        'created_by',
        'tgl_opname',
    ];

    public function barang()
    {
        return $this->belongsTo(WpmMasterBarangModel::class, 'barang_id');
    }

    public function soh()
    {
        return $this->belongsTo(WpmSohModel::class, 'soh_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
