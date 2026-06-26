<?php

namespace App\Models\Vehicle;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $table = 'vehicles';

    protected $fillable = [
        'no_pol',
        'vendor',
    ];

    public function transactions()
    {
        return $this->hasMany(VehicleTransaction::class, 'vehicle_id');
    }
}
