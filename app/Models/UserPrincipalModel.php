<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserPrincipalModel extends Model
{
    use HasFactory;

    protected $table = 'user_principals';

    protected $fillable = ['user_id', 'principal'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
