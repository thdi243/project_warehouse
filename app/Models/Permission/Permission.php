<?php

namespace App\Models\Permission;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $fillable = [
        'name',
        'section',
        'description'
    ];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function roles()
    {
        return $this->belongsToMany(\App\Models\Role::class)->withTimestamps();
    }
}
