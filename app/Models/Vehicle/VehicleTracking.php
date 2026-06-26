<?php

namespace App\Models\Vehicle;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleTracking extends Model
{
    use HasFactory;

    protected $table = 'vehicle_tracking';

    protected $fillable = [
        'vehicle_transaction_id',
        'location_id',
        'arrival_time',
        'departure_time',
        'duration_seconds',
        'status_notes',
        'created_by',
    ];

    protected $casts = [
        'arrival_time' => 'datetime',
        'departure_time' => 'datetime',
    ];

    public function transaction()
    {
        return $this->belongsTo(VehicleTransaction::class, 'vehicle_transaction_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
