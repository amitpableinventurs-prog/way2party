<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'app_user_id',
        'event_id',
        'subject',
        'message',
        'priority',
        'status',
        'last_reply_at',
    ];

    protected $casts = [
        'last_reply_at' => 'datetime',
    ];

    public function appUser()
    {
        return $this->belongsTo(AppUser::class, 'app_user_id');
    }

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    public function replies()
    {
        return $this->hasMany(SupportTicketReply::class);
    }
}
