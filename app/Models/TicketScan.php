<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketScan extends Model
{
    use HasFactory;

    protected $fillable = [
        'scanner_id',
        'event_id',
        'order_child_id',
        'ticket_number',
        'result',
        'message',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    public function scanner()
    {
        return $this->belongsTo(User::class, 'scanner_id');
    }

    public function orderChild()
    {
        return $this->belongsTo(OrderChild::class, 'order_child_id');
    }
}
