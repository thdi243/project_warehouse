<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserSignatureModel extends Model
{
    use HasFactory;

    protected $table = 'user_signature';

    protected $fillable = [
        'user_id',
        'signature'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
