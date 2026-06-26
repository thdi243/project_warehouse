<?php

namespace App\Models\Vehicle;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class VehicleVendor extends Model
{
    use HasFactory;

    protected $table = 'vehicle_vendors';

    protected $fillable = [
        'name',
        'description',
    ];
}
