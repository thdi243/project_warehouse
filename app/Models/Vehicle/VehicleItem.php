<?php

namespace App\Models\Vehicle;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleItem extends Model
{
    use HasFactory;

    protected $table = 'vehicle_items';

    protected $fillable = [
        // 'sku',
        'name',
    ];
}
