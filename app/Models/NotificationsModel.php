<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Events\ShowPortalNotification;

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

    protected static function booted()
    {
        static::saved(function ($notification) {
            try {
                event(new ShowPortalNotification([
                    'id'         => $notification->id,
                    'user_id'    => $notification->user_id,
                    'title'      => $notification->title,
                    'message'    => $notification->message,
                    'url'        => $notification->url,
                    'created_at' => $notification->created_at ? $notification->created_at->format('d F Y, H:i') : now()->format('d F Y, H:i'),
                    'is_read'    => $notification->is_read,
                ]));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Failed to broadcast notification: " . $e->getMessage());
            }
        });
    }

    public function notifiable()
    {
        return $this->morphTo();
    }
}
