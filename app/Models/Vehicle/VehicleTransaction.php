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
        'vehicle_id',
        'jenis',
        'vendor',
        'item_id',
        'no_spb',
        'qty_spb',
        'target_location_id',
        'current_location_id',
        'status',
        'qc_status',
        'unloading_status',
        'no_antrian',
        'check_in_time',
        'check_out_time',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
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

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
