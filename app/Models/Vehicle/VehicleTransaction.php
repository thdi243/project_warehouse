<?php

namespace App\Models\Vehicle;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleTransaction extends Model
{
    use HasFactory;

    protected $table = 'vehicle_transactions';

    protected $fillable = [
        'no_transaction',
        'trnvisitorid',
        'vehicle_id',
        'jenis',
        'vendor',
        'nama_driver',
        'no_hp_driver',
        'checkin_pos1',
        'item_id',
        'no_spb',
        'qty_spb',
        'target_location_id',
        'current_location_id',
        'status',
        'qc_status',
        'start_sampling_time',
        'start_sampling_by',
        'finish_sampling_time',
        'finish_sampling_by',
        'unloading_status',
        'no_antrian',
        'queue_taken_time',
        'queue_taken_by',
        'start_loading_time',
        'start_loading_by',
        'finish_loading_time',
        'finish_loading_by',
        'timbangan_out_time',
        'timbangan_out_by',
        'follow_up_time',
        'follow_up_target',
        'follow_up_notes',
        'follow_up_by',
        'check_in_time',
        'check_out_time',
        'check_out_by',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'checkin_pos1' => 'datetime',
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
        'queue_taken_time' => 'datetime',
        'start_sampling_time' => 'datetime',
        'finish_sampling_time' => 'datetime',
        'start_loading_time' => 'datetime',
        'finish_loading_time' => 'datetime',
        'timbangan_out_time' => 'datetime',
        'follow_up_time' => 'datetime',
        'qty_spb' => 'decimal:2',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    public function item()
    {
        return $this->belongsTo(VehicleItem::class, 'item_id');
    }

    public function targetLocation()
    {
        return $this->belongsTo(Location::class, 'target_location_id');
    }

    public function currentLocation()
    {
        return $this->belongsTo(Location::class, 'current_location_id');
    }

    public function tracking()
    {
        return $this->hasMany(VehicleTracking::class, 'vehicle_transaction_id');
    }

    public function activeTracking()
    {
        return $this->hasOne(VehicleTracking::class, 'vehicle_transaction_id')->whereNull('departure_time')->latestOfMany();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function checkInBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function queueTakenBy()
    {
        return $this->belongsTo(User::class, 'queue_taken_by');
    }

    public function startSamplingBy()
    {
        return $this->belongsTo(User::class, 'start_sampling_by');
    }

    public function finishSamplingBy()
    {
        return $this->belongsTo(User::class, 'finish_sampling_by');
    }

    public function startLoadingBy()
    {
        return $this->belongsTo(User::class, 'start_loading_by');
    }

    public function finishLoadingBy()
    {
        return $this->belongsTo(User::class, 'finish_loading_by');
    }

    public function timbanganOutBy()
    {
        return $this->belongsTo(User::class, 'timbangan_out_by');
    }

    public function checkOutBy()
    {
        return $this->belongsTo(User::class, 'check_out_by');
    }
}
