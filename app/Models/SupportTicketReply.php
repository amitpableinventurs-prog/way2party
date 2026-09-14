<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportTicketReply extends Model
{
    use HasFactory;

    protected $fillable = [
        'support_ticket_id',
        'sender_type',
        'sender_id',
        'body',
    ];

    protected $appends = ['sender'];

    public function ticket()
    {
        return $this->belongsTo(SupportTicket::class, 'support_ticket_id');
    }

    public function getSenderAttribute()
    {
        if ($this->attributes['sender_type'] == 'app_user') {
            return AppUser::find($this->attributes['sender_id'], ['id', 'name', 'last_name', 'image']);
        }
        return User::find($this->attributes['sender_id'], ['id', 'first_name', 'last_name', 'image']);
    }
}
