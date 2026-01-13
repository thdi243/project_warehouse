<?php

namespace App\Models\Permission;

use Illuminate\Database\Eloquent\Model;

class UserPermission extends Model
{
    protected $table = 'permission_user';

    protected $fillable = [
        'user_id',
        'permission_id',
    ];
}
