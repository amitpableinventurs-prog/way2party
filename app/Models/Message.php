<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'sender_type',
        'sender_id',
        'body',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    protected $appends = ['sender'];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function getSenderAttribute()
    {
        if ($this->attributes['sender_type'] == 'app_user') {
            return AppUser::find($this->attributes['sender_id'], ['id', 'name', 'last_name', 'image']);
        }
        return User::find($this->attributes['sender_id'], ['id', 'first_name', 'last_name', 'organization_name', 'image']);
    }
}
