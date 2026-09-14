<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'app_user_id',
        'user_id',
        'event_id',
        'last_message_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    public function appUser()
    {
        return $this->belongsTo(AppUser::class, 'app_user_id');
    }

    public function organizer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Unread count for whichever side of the conversation is NOT $senderType,
     * i.e. messages sent by the other party that this viewer hasn't read yet.
     */
    public function unreadCountFor(string $viewerType)
    {
        return $this->messages()
            ->where('sender_type', '!=', $viewerType)
            ->whereNull('read_at')
            ->count();
    }
}
