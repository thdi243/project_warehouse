<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NotificationsModel extends Model
{
    use HasFactory;

    protected $table = 'notifications';

    protected $fillable = [
        'user_id',
        'notifiable_type',
        'notifiable_id',
        'title',
        'message',
        'url',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function notifiable()
    {
        return $this->morphTo();
    }
}
